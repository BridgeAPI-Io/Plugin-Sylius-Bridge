<?php

declare(strict_types=1);

namespace Bridge\SyliusBridgePlugin\Controller\Action;

use Doctrine\ORM\EntityManagerInterface;
use Safe\Exceptions\JsonException;
use Sylius\Component\Core\Model\Order;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

use function Safe\json_encode;

final class UpdateSelectedBankOnPaymentAction
{
    public function __construct(
        private RepositoryInterface $orderRepository,
        private EntityManagerInterface $paymentManager
    ) {
    }

    /**
     * @throws JsonException
     */
    public function __invoke(Request $request): JsonResponse
    {
        $orderId = $request->get('orderId');
        $bankId = $request->get('bankId');

        //@phpstan-ignore-next-line
        if (empty($orderId) || empty($bankId)) {
            return new JsonResponse(json_encode(['status' => false]));
        }

        /** @var Order $order */
        $order = $this->orderRepository->findOneBy(['id' => $orderId]);

        $payment = $order->getLastPayment();

        if ($payment === null) {
            return new JsonResponse(json_encode(['status' => false]));
        }

        $payment->setBankId((int) $bankId);

        $this->paymentManager->flush();

        return new JsonResponse(json_encode(['status' => true]));
    }
}
