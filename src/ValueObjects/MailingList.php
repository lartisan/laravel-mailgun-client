<?php

namespace Lartisan\MailgunClient\ValueObjects;

use Illuminate\Support\Collection;

readonly class MailingList
{
    public function __construct(
        public string $access_level,
        public string $address,
        public string $created_at,
        public string $description,
        public ?int $members_count,
        public string $name,
        public string $reply_preference,
    ) {}

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @return Collection<int, MailingList>
     */
    public static function toCollection(array $items): Collection
    {
        return collect($items)
            ->map(fn (array $item) => self::from($item));
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function from(array $data): MailingList
    {
        return new self(
            access_level: $data['access_level'],
            address: $data['address'],
            created_at: $data['created_at'],
            description: $data['description'],
            members_count: $data['members_count'],
            name: $data['name'],
            reply_preference: $data['reply_preference'],
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'access_level' => $this->access_level,
            'address' => $this->address,
            'created_at' => $this->created_at,
            'description' => $this->description,
            'members_count' => $this->members_count,
            'name' => $this->name,
            'reply_preference' => $this->reply_preference,
        ];
    }
}
