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
            setcookie("device", $deviceid, 2147483647); // The maximum so far to expire: 2^31 - 1 = 2147483647 = 2038-01-19 04:14:07

            $key = $this->security->getToken(50);
            setcookie("key", $this->security->encryption($key, $deviceid), 2147483647); // The maximum so far to expire: 2^31 - 1 = 2147483647 = 2038-01-19 04:14:07
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
        $this->redbean->store( $item );
        
        // Retrieve this device links
        $items = $this->redbean->find( 'linkamelink', ' `key` = ? ORDER BY id DESC ', [$key] );
    }
    else {
        // For only one device setup, retrieve all
        $items = $this->redbean->findAll( 'linkamelink', ' ORDER BY id DESC ' );
    }

    // Render index view
    return $this->renderer->render($response, 'index.phtml', ['links' => $this->redbean->exportAll($items), 'device' => $deviceid]);
});

$app->get('/device/{deviceid}', function ($request, $response, $args) {
    
    $this->logger->info("'/device/' get route");

    $deviceid = $args['deviceid'];

    // Retrieve mobile ip
    $ip = $request->getAttribute('ip_address');
    //$ipcrypt = $this->security->encryption($ip, $deviceid);

    // Look for this device
    $devices = $this->redbean->findAll( 'linkamedevice', ' ORDER BY id DESC ' );

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
    //return $response->withBody($key)->withStatus(302);
    return $response->withHeader('Content-Type', 'text/plain')->write($key)->withStatus(302);
});

$app->get('/links[/{id}]', function ($request, $response, $args) {
    
    $this->logger->info("'/links/' route");
    
    // retrieve all links or selected id
    if (isset($args['id']))
    {
        $items = $this->redbean->load( 'linkamelink', $args['id'] );
    }
    else
    {
        $items = $this->redbean->findAll( 'linkamelink' );
    }

    // Return results
    return $response->withJson($items);
});

$app->post('/link', function ($request, $response, $args) {
    
    $this->logger->info("'/link/' post route");
    
    $item = $this->redbean->dispense( 'linkamelink' );
    $item->import( $request->getParsedBody() );
    $this->redbean->store( $item );

    // Return result
    return $response->withJson($item)->withStatus(201);
});

$app->delete('/link/{id}', function ($request, $response, $args) {
    
    $this->logger->info("'/link/' delete route");

    // Retrieve selected id
    $item = $this->redbean->load( 'linkamelink', $args['id'] );
    $this->redbean->trash( $item );

    // Return result
    return $response->withStatus(200);
});