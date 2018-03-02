<?php

return [

    /**
     * Enable auto validation for controller actions.
     */
    'validation' => true,

    /**
     * Enable middlewares.
     */
    'middlewares' => true,

    /**
     * Enable persistence.
     */
    'database' => true,

    /**
     * Enable API authentication
     *
     * Dependencies:
     * -> database
     * -> middlewares
     */
    'authentication' => true,

    /**
     * Enable auto CRUD and single request backend.
     *
     * Dependencies:
     * -> database
     */
    'engine' => true

];