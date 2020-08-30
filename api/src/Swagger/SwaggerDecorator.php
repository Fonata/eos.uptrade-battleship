<?php
// api/src/Swagger/SwaggerDecorator.php

namespace App\Swagger;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * This class documents further endpoints that are not CRUD-related.
 */
final class SwaggerDecorator implements NormalizerInterface
{
    private $decorated;

    public function __construct(NormalizerInterface $decorated)
    {
        $this->decorated = $decorated;
    }

    public function normalize($object, string $format = null, array $context = [])
    {
        /** @var \ArrayObject $docs */
        $docs = $this->decorated->normalize($object, $format, $context);

        $docs['paths']['/api/user/{id}']['delete']['summary'] = "Delete the user along with all of his or her games.";

        $docs['paths']['/login']['post']['description'] = 'API endpoint to sign users in.';
        $docs['paths']['/login']['post']['requestBody'] = [
            'required' => true,
            'content' => [
                'application/json' => ['schema' => [
                    'type' => 'object',
                    'properties' => [
                        'email' => [
                            'type' => 'string',
                            'example' => 'admin@eos-uptrade.de',
                            'description' => 'The email address of the user to log in.'
                        ],
                        'password' => ['type' => 'string', 'example' => 'demo']
                    ],
                    'required' => ['email', 'password']]]
            ]
        ];
        $docs['paths']['/login']['post']['responses'] = [
            '200' => ['description' => 'Success'],
            '403' => ['description' => 'Failure'],
        ];


        return $docs;
    }

    public function supportsNormalization($data, string $format = null)
    {
        return $this->decorated->supportsNormalization($data, $format);
    }
}
