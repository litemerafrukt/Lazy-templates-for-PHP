<?php

/********************************************************************
* Output - use lazy templates to render the arrays.
********************************************************************/
require '../ltpl.php';

echo Ltpl::render('superglobals.ltpl.html', [
    'serverglobal' => Ltpl::mapArray('serverkey', 'servervalue', $_SERVER),
]);
