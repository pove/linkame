<?php
// Routes

/*$app->get('/[{name}]', function ($request, $response, $args) {
    // Sample log message
    $this->logger->info("'/' route");

    // Render index view
    return $this->renderer->render($response, 'index.phtml', $args);
});*/

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