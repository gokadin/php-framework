<?php

namespace Library\Http;

interface RequestContract
{
    function authorize();

    function rules();
}