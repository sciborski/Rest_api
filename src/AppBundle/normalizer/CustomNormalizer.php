<?php

namespace AppBundle\normalizer;

use FOS\RestBundle\Normalizer\ArrayNormalizerInterface as ArrayNormalizerInterface;
use FOS\RestBundle\Normalizer\Exception;


class CustomNormalizer implements ArrayNormalizerInterface
{

    public function normalize(array $data)
    {
        foreach ( $data as $d ){
            echo $d;
        }

        // TODO: Implement normalize() method.
    }
}

