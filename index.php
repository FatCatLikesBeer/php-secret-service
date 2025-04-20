<?php

declare(strict_types=1);

include('./router.php');
include('./controllers/controller.php');

// API Routing
get('/api/v0', function () {
  route_not_used();
});

get('/api/v0/messages/$uuid', function ($uuid) {
  get_message($uuid);
});

// Create tables first before moving forward.
// Come up with an analogy or something
