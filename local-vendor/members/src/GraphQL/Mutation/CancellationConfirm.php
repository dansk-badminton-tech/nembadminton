<?php


namespace FlyCompany\Members\GraphQL\Mutation;

final class CancellationConfirm
{

    public function __invoke(mixed $root, array $args): array
    {
        return [
            'email_attached' => false,
            'email_pattern' => 'da***@gmail.com',
        ];
    }

}
