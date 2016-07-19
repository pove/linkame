<?php
// Routes


$app->get('/', function ($request, $response, $args) {
    // Home log message
    $this->logger->info("'/' route");

    $deviceid = '';
    $usedevices = $this->get('settings')['security']['usedevices'];
    // If we are using serveral devices setup, filter by key
    if ($usedevices)
    {
        $key = '';
        $item = null;
        if (!isset($_COOKIE["device"]) || !isset($_COOKIE["key"]))
        {
            // If this device does not exist, create one            
            $deviceid = $this->security->getToken(5);
            setcookie("device", $deviceid, 2147483647, '/'); // The maximum so far to expire: 2^31 - 1 = 2147483647 = 2038-01-19 04:14:07

            $key = $this->security->getToken(50);
            setcookie("key", $this->security->encryption($key, $deviceid), 2147483647, '/'); // The maximum so far to expire: 2^31 - 1 = 2147483647 = 2038-01-19 04:14:07
        }
        else {
            // Retrieve device and key from cookies
            $deviceid = $_COOKIE["device"];
            $key = $this->security->decryption($_COOKIE["key"], $deviceid);

            // Get this device record
            $item = $this->redbean->findOne( 'linkamedevice', ' `key` = ? ', [ $key ] );
        }

        // Create new record for this device
        if ($item === null)
        {
            $item = $this->redbean->dispense( 'linkamedevice' );
            $item->key = $key;
        }

        // Update device IP
        $ip = $request->getAttribute('ip_address');
        $item->ip = $this->security->encryption($ip, $deviceid);
        $item->ipupdated = $this->redbean->isoDateTime();
        $this->redbean->store( $item );
        
        // Retrieve this device links
        $items = $this->redbean->find( 'linkamelink', ' `key` = ? ORDER BY id DESC ', [$key] );
        $itemsresult = $this->redbean->exportAll($items);
        
        // Decrypt links
        for ($i=0; $i < count($itemsresult); $i++) {            
            $itemsresult[$i]['name'] = $this->security->decryption($itemsresult[$i]['name'], $deviceid);
            $itemsresult[$i]['url'] = $this->security->decryption($itemsresult[$i]['url'], $deviceid);
            unset($itemsresult[$i]['key']);
        }
    }
    else {
        // For only one device setup, retrieve all
        $items = $this->redbean->findAll( 'linkamelink', ' ORDER BY id DESC ' );
        $itemsresult = $this->redbean->exportAll($items);
    }

    // Render index view
    return $this->renderer->render($response, 'index.phtml', ['links' => $itemsresult, 'device' => $deviceid]);
});

$app->get('/device/{deviceid}', function ($request, $response, $args) {
    
    $this->logger->info("'/device/' get route");

    $deviceid = $args['deviceid'];

    // Retrieve mobile ip
    $ip = $request->getAttribute('ip_address');
    //HACK: to test in localhost
    //$ip = '192.168.0.X';

    // Look for this device
    $devices = $this->redbean->findAll( 'linkamedevice', ' ORDER BY id DESC ' );

    // Check if they are on the same ip
    foreach ($devices as $device)
    {
        if ($this->security->decryption($device->ip, $deviceid) === $ip)
        {
            $key =  $this->security->encryption($device->key, $deviceid);
            break;
        }
    }

    if (!isset($key))
    {
        return $response->withStatus(404, 'Device not found.');
    }

    // Return result
    return $response->withHeader('Content-Type', 'text/plain')->write($key)->withStatus(200);
});

$app->get('/links[/{device}]', function ($request, $response, $args) {
    
    $this->logger->info("'/links/' route");
    
    $usedevices = $this->get('settings')['security']['usedevices'];
    // If we are using serveral devices setup, filter by key
    if ($usedevices)
    {
        // Retrieve links from key
        if (isset($args['device']) && isset($request->getQueryParams()['key']))
        {
            $key = $this->security->decryption($request->getQueryParams()['key'], $args['device']);
            $items = $this->redbean->find( 'linkamelink', ' `key` = ? ORDER BY id DESC ', [$key] );
            
            $itemsresult = $this->redbean->exportAll($items);
            
            // Decrypt links
            for ($i=0; $i < count($itemsresult); $i++) {            
                $itemsresult[$i]['name'] = $this->security->decryption($itemsresult[$i]['name'], $args['device']);
                $itemsresult[$i]['url'] = $this->security->decryption($itemsresult[$i]['url'], $args['device']);
                unset($itemsresult[$i]['key']);
            }
        }
    }
    else
    {
         // Retrieve all links
        $items = $this->redbean->find( 'linkamelink', ' `key` IS NULL ORDER BY id DESC ' );
        $itemsresult = $this->redbean->exportAll($items);
    }

    // If no links found
    //if (!isset($itemsresult) || $itemsresult === null || count($itemsresult) == 0)
    //    return $response->withStatus(404);

    // Return results
    return $response->withJson($itemsresult);
});

$app->post('/link[/{device}]', function ($request, $response, $args) {
    
    $this->logger->info("'/link/' post route");
    
    $item = $this->redbean->dispense( 'linkamelink' );
    $item->import( $request->getParsedBody() );

    $usedevices = $this->get('settings')['security']['usedevices'];
    // If we are using serveral devices setup, encrypt
    if ($usedevices)
    {
        // Set key if we have it
        if (isset($args['device']) && isset($request->getQueryParams()['key']))
        {
            $item->name = $this->security->encryption($item->name, $args['device']);
            $item->url = $this->security->encryption($item->url, $args['device']);
            $item->key = $this->security->decryption($request->getQueryParams()['key'], $args['device']);
        }
    }

    $this->redbean->store( $item );

    // Return result
    return $response->withJson($item)->withStatus(201);
});

$app->delete('/link/{id}[/{device}]', function ($request, $response, $args) {
    
    $this->logger->info("'/link/' delete route");

    $usedevices = $this->get('settings')['security']['usedevices'];
    // If we are using serveral devices setup, get key to delete
    if ($usedevices)
    {
        // Retrieve link from key
        if (isset($args['device']) && isset($request->getQueryParams()['key']))
        {
            $key = $this->security->decryption($request->getQueryParams()['key'], $args['device']);
            $item = $this->redbean->findone( 'linkamelink', ' id = ? AND `key` = ? ORDER BY id DESC ', [$args['id'], $key] );
        }
    }
    else
    {
        // Retrieve selected id
        $item = $this->redbean->load( 'linkamelink', $args['id'] );
    }

    if (isset($item) && $item !== null)
    {
        $this->redbean->trash( $item );
        // Return result
        return $response->withStatus(200);
    }
    else
    {        
        // Return not found
        return $response->withStatus(404);
    }
});