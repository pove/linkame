<?php
// Routes

$app->get('/links/[{id}]', function ($request, $response, $args) {
    
    $this->logger->info("'/links/' route");
    
    // retrieve all links or selected id
    if (isset($args['id']))
    {
        $items = $this->redbean->findAll( 'linkame_link' );
        $items = $this->redbean->load( 'linkame_link', $args['id'] );
    }
    else
    {
        $items = $this->redbean->findAll( 'linkame_link' );
    }

    // Return results
    return $response->withJson($items);
});

$app->post('/link/', function ($request, $response, $args) {
    
    $this->logger->info("'/link/' post route");
    
    $item = $this->redbean->dispense( 'linkame_link' );
    $item->import( $request->getParsedBody() );
    $this->redbean->store( $item );

    // Return result
    return $response->withJson($item)->withStatus(201);
});