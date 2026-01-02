<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\AdminConfig;
use App\Models\TermsAcceptance;
use App\Models\Project;
use App\Models\Proposal;
use App\Models\Contract;
use App\Services\AssasService;
use App\Services\NotificationService;

/**
 * PaymentController - Gerenciamento de Pagamentos
 */
class PaymentController extends Controller
{
    protected Payment $paymentModel;
    protected Subscription $subscriptionModel;
    protected Project $projectModel;
    protected Proposal $proposalModel;
    protected Contract $contractModel;

    public function __construct()
    {
        $this->paymentModel = new Payment();
        $this->subscriptionModel = new Subscription();
        $this->projectModel = new Project();
        $this->proposalModel = new Proposal();
        $this->contractModel = new Contract();
    }

    public function plans(): void
    {
        $planModel = new SubscriptionPlan();
        $plans = $planModel->getAllActiveWithFeatures();

        $this->setLayout('dashboard');
        $this->view('payments/plans', [
            'title' => 'Planos de Assinatura',
            'plans' => $plans,
        ]);
    }

    public function checkout(int $planId): void
    {
        $user = $this->currentUser();
        $planModel = new SubscriptionPlan();
        $plan = $planModel->getWithDecodedFeatures($planId);

        if (!$plan) {
            $this->flash('error', 'Plano não encontrado.');
            $this->redirect('plans');
        }

        $userModel = new User();
        $userData = $userModel->find($user['id']);

        $this->setLayout('dashboard');
        $this->view('payments/checkout', [
            'title' => 'Checkout - ' . $plan['name'],
            'plan' => $plan,
            'userData' => $userData,
            'csrf' => $this->generateCsrfToken(),
        ]);
    }

    public function processCheckout(): void
    {
        if (!$this->validateCsrf()) {
            $this->json(['error' => 'Token inválido'], 400);
            return;
        }

        $user = $this->currentUser();
        $planId = (int) $this->input('plan_id');

        $planModel = new SubscriptionPlan();
        $plan = $planModel->find($planId);

        if (!$plan) {
            $this->json(['error' => 'Plano não encontrado'], 404);
            return;
        }

        // Verificar aceite de termos
        if (!$this->input('accept_terms')) {
            $this->json(['error' => 'Você deve aceitar os Termos de Uso para continuar.'], 400);
            return;
        }

        try {
            // Registrar aceite de termos
            $termsModel = new TermsAcceptance();
            $termsModel->recordAcceptance($user['id'], '1.0');
            // Verificar chave do ASSAS para decidir se vamos simular ou chamar a API real
            $configModel = new AdminConfig();
            $assasApiKey = $configModel->get('assas_api_key', '');

            // Modo simulado (sem integração externa)
            if (empty($assasApiKey)) {
                $this->json([
                    'error' => 'Pagamento indisponível. Configuração ASSAS ausente.',
                ], 400);
            }

            // Modo real: integração com ASSAS
            $assas = new AssasService();

            // Cancelar assinatura ativa existente antes de criar nova (evita duplicidade)
            $existing = $this->subscriptionModel->getActiveByCompany($user['id']);
            if ($existing) {
                $assasId = (string)($existing['assas_subscription_id'] ?? '');
                if ($assasId !== '' && substr($assasId, 0, 10) !== 'SIMULATED-') {
                    $assas->cancelSubscription($assasId);
                }
                $this->subscriptionModel->cancel($existing['id']);
            }

            // Criar/atualizar cliente no ASSAS
            $customerData = [
                'name' => $this->input('name'),
                'email' => $this->input('email'),
                'cpfCnpj' => preg_replace('/\D/', '', $this->input('cpf')),
                'phone' => preg_replace('/\D/', '', $this->input('phone')),
                'postalCode' => preg_replace('/\D/', '', $this->input('zip_code')),
                'address' => $this->input('street'),
                'addressNumber' => $this->input('number'),
                'complement' => $this->input('complement'),
                'province' => $this->input('neighborhood'),
            ];

            $customer = $assas->findCustomerByEmail($customerData['email']);
            if (!$customer) {
                $customer = $assas->createCustomer($customerData);
            }

            // Garantir que não existam assinaturas ativas no ASSAS para este cliente
            $assasActives = $assas->listSubscriptionsByCustomer($customer['id'], 'ACTIVE');
            $assasActiveList = $assasActives['data'] ?? [];
            foreach ($assasActiveList as $asub) {
                if (!empty($asub['id'])) {
                    $assas->cancelSubscription($asub['id']);
                    // Refletir cancelamento no banco local, se existir registro
                    $rows = $this->subscriptionModel->query(
                        "SELECT id FROM company_subscriptions WHERE assas_subscription_id = :assas AND company_id = :company_id AND status = 'active' LIMIT 10",
                        ['assas' => $asub['id'], 'company_id' => $user['id']]
                    );
                    foreach ($rows as $r) {
                        $this->subscriptionModel->cancel((int)$r['id']);
                    }
                }
            }

            // Criar assinatura
            $subscriptionData = [
                'customer' => $customer['id'],
                'billingType' => 'CREDIT_CARD',
                'value' => $plan['price'],
                'cycle' => 'MONTHLY',
                'description' => 'Assinatura ' . $plan['name'],
                'creditCard' => [
                    'holderName' => $this->input('card_holder'),
                    'number' => preg_replace('/\D/', '', $this->input('card_number')),
                    'expiryMonth' => $this->input('card_month'),
                    'expiryYear' => $this->input('card_year'),
                    'ccv' => $this->input('card_cvv'),
                ],
                'creditCardHolderInfo' => [
                    'name' => $this->input('name'),
                    'email' => $this->input('email'),
                    'cpfCnpj' => preg_replace('/\D/', '', $this->input('cpf')),
                    'postalCode' => preg_replace('/\D/', '', $this->input('zip_code')),
                    'addressNumber' => $this->input('number'),
                    'phone' => preg_replace('/\D/', '', $this->input('phone')),
                ],
            ];

            $subscription = $assas->createSubscription($subscriptionData);

            // Salvar assinatura no banco
            $this->subscriptionModel->create([
                'company_id' => $user['id'],
                'plan_id' => $planId,
                'status' => 'active',
                'assas_subscription_id' => $subscription['id'],
                'assas_customer_id' => $customer['id'],
                'current_period_start' => date('Y-m-d'),
                'current_period_end' => date('Y-m-d', strtotime('+1 month')),
            ]);

            // Registrar pagamento
            $this->paymentModel->create([
                'user_id' => $user['id'],
                'type' => 'subscription',
                'amount' => $plan['price'],
                'description' => 'Assinatura ' . $plan['name'],
                'assas_payment_id' => $subscription['id'],
                'status' => 'confirmed',
                'paid_at' => date('Y-m-d H:i:s'),
            ]);

            $this->json([
                'success' => true,
                'message' => 'Assinatura realizada com sucesso!',
                'redirect' => $this->url('payment/success'),
            ]);

        } catch (\Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    public function success(): void
    {
        $this->setLayout('dashboard');
        $this->view('payments/success', ['title' => 'Pagamento Confirmado']);
    }

    public function failure(): void
    {
        $this->setLayout('dashboard');
        $this->view('payments/failure', ['title' => 'Falha no Pagamento']);
    }

    public function cancelled(): void
    {
        $this->setLayout('dashboard');
        $this->view('payments/cancelled', ['title' => 'Assinatura Cancelada']);
    }

    public function history(): void
    {
        $user = $this->currentUser();
        $payments = $this->paymentModel->getByUser($user['id']);

        $this->setLayout('dashboard');
        $this->view('payments/history', [
            'title' => 'Histórico de Pagamentos',
            'payments' => $payments,
        ]);
    }

    public function subscription(): void
    {
        $user = $this->currentUser();
        $subscription = $this->subscriptionModel->getActiveByCompany($user['id']);

        $this->setLayout('dashboard');
        $this->view('payments/subscription', [
            'title' => 'Minha Assinatura',
            'subscription' => $subscription,
        ]);
    }

    public function cancelSubscription(): void
    {
        $user = $this->currentUser();
        $subscription = $this->subscriptionModel->getActiveByCompany($user['id']);

        $isAjax = (strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest')
            || (stripos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false);

        if (!$subscription) {
            if ($isAjax) { $this->json(['error' => 'Assinatura não encontrada'], 404); }
            $this->flash('error', 'Assinatura não encontrada.');
            $this->redirect('subscription');
        }

        try {
            $configModel = new AdminConfig();
            $assasApiKey = $configModel->get('assas_api_key', '');

            if (empty($assasApiKey) || substr((string)($subscription['assas_subscription_id'] ?? ''), 0, 10) === 'SIMULATED-') {
                $this->subscriptionModel->cancel($subscription['id']);
                if ($isAjax) { $this->json(['success' => true, 'message' => 'Assinatura cancelada (simulação).']); }
                $this->flash('success', 'Assinatura cancelada.');
                $this->redirect('subscription/cancelled');
            }

            $assas = new AssasService();
            $assas->cancelSubscription($subscription['assas_subscription_id']);
            $this->subscriptionModel->cancel($subscription['id']);

            if ($isAjax) { $this->json(['success' => true, 'message' => 'Assinatura cancelada.']); }
            $this->flash('success', 'Assinatura cancelada.');
            $this->redirect('subscription/cancelled');
        } catch (\Exception $e) {
            if ($isAjax) { $this->json(['error' => $e->getMessage()], 500); }
            $this->flash('error', $e->getMessage());
            $this->redirect('subscription');
        }
    }

    public function webhook(): void
    {
        $payload = json_decode(file_get_contents('php://input'), true);

        if (!$payload || !isset($payload['event'])) {
            http_response_code(400);
            exit;
        }

        // Processar eventos do ASSAS
        switch ($payload['event']) {
            case 'PAYMENT_CONFIRMED':
            case 'PAYMENT_RECEIVED':
                $this->handlePaymentConfirmed($payload['payment']);
                break;
            case 'PAYMENT_OVERDUE':
                $this->handlePaymentOverdue($payload['payment']);
                break;
        }

        http_response_code(200);
    }

    protected function handlePaymentConfirmed(array $payment): void
    {
        $existing = $this->paymentModel->findByAssasId($payment['id']);
        if ($existing) {
            $this->paymentModel->updateStatus($existing['id'], 'confirmed');
        }
    }

    protected function handlePaymentOverdue(array $payment): void
    {
        $existing = $this->paymentModel->findByAssasId($payment['id']);
        if ($existing) {
            $this->paymentModel->updateStatus($existing['id'], 'failed');
        }
    }

    public function processProposalPayment(int $projectId, int $proposalId): void
    {
        if (!$this->validateCsrf()) {
            $this->json(['error' => 'Token inválido'], 400);
            return;
        }

        $user = $this->currentUser();
        $project = $this->projectModel->find($projectId);

        if (!$project || (int) $project['company_id'] !== (int) $user['id']) {
            $this->json(['error' => 'Não autorizado'], 403);
            return;
        }

        $proposal = $this->proposalModel->find($proposalId);
        if (!$proposal || (int) $proposal['project_id'] !== $projectId) {
            $this->json(['error' => 'Proposta não encontrada'], 404);
            return;
        }

        if ($proposal['status'] === 'paid') {
            $this->json(['success' => true, 'message' => 'Essa proposta já foi paga.']);
            return;
        }

        if ($proposal['status'] !== 'accepted_pending_payment') {
            $this->json(['error' => 'A proposta ainda não está liberada para pagamento.'], 400);
            return;
        }

        if (!$this->input('accept_terms')) {
            $this->json(['error' => 'É necessário aceitar os termos para prosseguir com o pagamento.'], 400);
            return;
        }

        $existingPayment = $this->paymentModel->findProposalPayment($proposalId);
        if ($existingPayment && in_array($existingPayment['status'], ['confirmed', 'received'], true)) {
            $this->proposalModel->markPaid($proposalId);
            $this->json(['success' => true, 'message' => 'Pagamento já confirmado anteriormente.']);
            return;
        }

        // garantir que o contrato esteja sincronizado com os valores atuais
        $this->contractModel->syncFromProposal($proposalId);

        $amount = (float) $proposal['proposed_value'];
        $description = 'Pagamento da proposta do projeto "' . $project['title'] . '"';

        $paymentPayload = [
            'user_id' => $user['id'],
            'type' => 'proposal',
            'reference_id' => $proposalId,
            'amount' => $amount,
            'description' => $description,
            'payment_method' => 'credit_card',
            'status' => 'pending',
        ];

        $configModel = new AdminConfig();
        $assasApiKey = $configModel->get('assas_api_key', '');

        try {
            // Registrar aceite de termos (versão 1.0 - pagamento de proposta)
            $termsModel = new TermsAcceptance();
            $termsModel->recordAcceptance($user['id'], '1.0-proposal');

            if (empty($assasApiKey)) {
                $paymentPayload['assas_payment_id'] = 'SIMULATED-PROPOSAL-' . uniqid();
                $paymentPayload['status'] = 'confirmed';
                $paymentPayload['paid_at'] = date('Y-m-d H:i:s');
            } else {
                $assas = new AssasService();

                $customerData = [
                    'name' => $this->input('billing_name', $user['name']),
                    'email' => $this->input('billing_email', $user['email']),
                    'cpfCnpj' => preg_replace('/\D/', '', (string) $this->input('billing_document', $user['document'] ?? '')),
                    'phone' => preg_replace('/\D/', '', (string) $this->input('billing_phone', '')),
                    'postalCode' => preg_replace('/\D/', '', (string) $this->input('billing_zip', '')),
                    'address' => $this->input('billing_street', ''),
                    'addressNumber' => $this->input('billing_number', ''),
                    'complement' => $this->input('billing_complement', ''),
                    'province' => $this->input('billing_neighborhood', ''),
                ];

                if (empty($customerData['cpfCnpj'])) {
                    $this->json(['error' => 'Informe o CPF/CNPJ para o pagamento.'], 400);
                    return;
                }

                $cardData = [
                    'holderName' => trim((string) $this->input('card_holder')),
                    'number' => preg_replace('/\D/', '', (string) $this->input('card_number')),
                    'expiryMonth' => $this->input('card_month'),
                    'expiryYear' => $this->input('card_year'),
                    'ccv' => $this->input('card_cvv'),
                ];

                if (in_array('', $cardData, true)) {
                    $this->json(['error' => 'Preencha todos os dados do cartão.'], 400);
                    return;
                }

                $assasPayment = $assas->createCreditCardPayment($customerData, $cardData, $amount, $description);
                $paymentPayload['assas_payment_id'] = $assasPayment['id'] ?? null;
                $paymentPayload['assas_invoice_url'] = $assasPayment['invoiceUrl'] ?? null;

                $status = strtoupper($assasPayment['status'] ?? 'PENDING');
                if (in_array($status, ['RECEIVED', 'CONFIRMED'], true)) {
                    $paymentPayload['status'] = 'confirmed';
                    $paymentPayload['paid_at'] = date('Y-m-d H:i:s');
                }
            }

            // Confirmação imediata para este fluxo
            if ($paymentPayload['status'] !== 'confirmed') {
                $paymentPayload['status'] = 'confirmed';
                $paymentPayload['paid_at'] = date('Y-m-d H:i:s');
            }

            $this->paymentModel->create($paymentPayload);
            $this->proposalModel->markPaid($proposalId);

            $notificationService = new NotificationService();
            $notificationService->notifyProposalPayment(
                (int) $proposal['professional_id'],
                (int) $projectId,
                $project['title'],
                $amount
            );
            $notificationService->notifyPaymentConfirmed(
                (int) $user['id'],
                $amount,
                $description
            );

            $this->json([
                'success' => true,
                'message' => 'Pagamento confirmado e recibo emitido.',
                'invoice_url' => $paymentPayload['assas_invoice_url'] ?? null,
            ]);
        } catch (\Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }
}
