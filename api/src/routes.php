<?php
// Routes

$app->get('/[{name}]', function ($request, $response, $args) {
    // Sample log message
    $this->logger->info("'/' route");
    
    // create test table record
    if (isset($args['name']))
    {
        $book = $this->redbean->dispense( 'book' );
        $book->name = $args['name'];
        $id = $this->redbean->store( $book );
        $args['id'] = $id;
    }

    // Render index view
    return $this->renderer->render($response, 'index.phtml', $args);
});


$app->get('/books/[{id}]', function ($request, $response, $args) {
    
    $this->logger->info("'/books/' route");
    
    // retrieve all books or selected id
    if (isset($args['id']))
    {
        $books = $this->redbean->findAll( 'book' );
        $books = $this->redbean->load( 'book', $args['id'] );
    }
    else
    {
        $books = $this->redbean->findAll( 'book' );
    }

    // Return results
    return $response->withJson($books);
});

$app->post('/books/', function ($request, $response, $args) {
    
    $this->logger->info("'/books/' post route");
    
    $book = $this->redbean->dispense( 'book' );
    $book->import( $request->getParsedBody() );
    $this->redbean->store( $book );

    // Return results
    return $response->withJson($book)->withStatus(201);
});