<?php

declare(strict_types=1);

namespace Bridge\SyliusBridgePlugin\Client;

use Symfony\Contracts\HttpClient\ResponseInterface;

interface BridgePaymentApiClientInterface
{
    public const STATUS_NEW = ['CREA', 'ACTC'];
    public const STATUS_PROCESSING = ['PART', 'PDNG'];
    public const STATUS_COMPLETED = ['ACSC'];
    public const STATUS_FAILED = ['RJCT'];

    public function setConfig(
        ?string $clientId,
        ?string $clientSecret,
        ?string $webhookSecret,
        ?string $testClientId,
        ?string $testClientSecret,
        ?string $testWebhookSecret,
    ): void;

    /** @return array{resources: array<int, array{id:int,name:string,country_code:string,logo_url:string,url: string,is_highlighted:bool,primary_color:string,secondary_color:string,parent_name:string,capabilities:array<int, string>,channel_type: array<int, string>,display_order:int,authentication_page_url:string,authentication_page_url_mobile:string}>}|null */
    public function getBanks(string $mode, ?string $localCode = null): ?array;

    public function createBridgeRequestPayment(string $body, string $mode = 'test'): ?ResponseInterface;

    /**
     * @return array{
     *   id: string,
     *   status: string,
     *   user: array{name: string, ip_address: string},
     *   sender: array<array-key, mixed>,
     *   transactions: list<array{
     *     id: string,
     *     status: string,
     *     amount: float,
     *     end_to_end_id: string,
     *     currency: string,
     *     label: string,
     *     client_reference: string,
     *     execution_date: string,
     *     beneficiary: array{iban: string,name: string,company_name: string},
     *   }>,
     *   bank_id: int,
     *   created_at: string,
     *   updated_at: string
     * }|null
     */
    public function getBridgeRequestPayment(string $id, string $mode = 'test'): ?array;
}
