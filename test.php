<?php

/**
 *  ____  ____  ____ _____    ___  __  __ ____
 * |  _ \|  _ \/ ___|_   _|  / _ \|  \/  / ___|
 * | |_) | |_) \___ \ | |   | | | | |\/| \___ \
 * |  _ <|  __/ ___) || |   | |_| | |  | |___) |
 * |_| \_\_|   |____/ |_|    \___/|_|  |_|____/
 *
 * @category    Application
 * @package     RpstOms
 */

declare(strict_types=1);

namespace App\Deposit\Domain\Entity;

use App\BadDebt\Domain\Entity\BadDebt;
use App\Customer\Domain\Entity\Customer;
use App\Deposit\Domain\ValueObject\TypeDepositStatus;
use App\Shipping\Domain\Entity\Shipping;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[ORM\Entity]
#[ORM\Table(name: 'trn_payment_invoice', options: ['comment' => '請求'])]
class PaymentInvoice
{
    use TimestampableEntity;

    #[ORM\Column(type: Types::BIGINT, nullable: false, options: ['comment' => '請求ID'])]
    #[ORM\Id,
        ORM\GeneratedValue()]
    private int $id;

    #[ORM\ManyToOne(targetEntity: Customer::class, inversedBy: 'paymentInvoices')]
    #[ORM\JoinColumn(name: 'customer_id', referencedColumnName: 'id', nullable: true, options: ['comment' => '顧客ID'])]
    private ?Customer $customer;

    #[ORM\OneToOne(targetEntity: Shipping::class)]
    #[ORM\JoinColumn(name: 'shipping_id', referencedColumnName: 'id', nullable: true, options: ['comment' => '出荷ID'])]
    private ?Shipping $shipping;

    #[ORM\Column(type: Types::INTEGER, nullable: false, options: ['comment' => '請求金額'])]
    private int $paymentInvoiceAmount;

    #[ORM\Column(type: Types::STRING, nullable: false, enumType: TypeDepositStatus::class, options: ['comment' => '入金ステータス区分'])]
    private TypeDepositStatus $typeDepositStatus;

    #[ORM\Column(type: Types::INTEGER, nullable: false, options: ['comment' => '送料'])]
    private int $shippingFee;

    #[ORM\Column(type: Types::INTEGER, nullable: false, options: ['comment' => '代引手数料'])]
    private int $codFee;

    #[ORM\Column(type: Types::INTEGER, nullable: false, options: ['comment' => 'オファー値引額'])]
    private int $offerDiscountAmount;

    #[ORM\Column(type: Types::INTEGER, nullable: false, options: ['comment' => '自社クーポン利用額'])]
    private int $coupon;

    #[ORM\Column(type: Types::INTEGER, nullable: false, options: ['comment' => '他社クーポン利用額'])]
    private int $externalCoupon;

    #[ORM\Column(type: Types::INTEGER, nullable: false, options: ['comment' => '自社ポイント使用額'])]
    private int $point;

    #[ORM\Column(type: Types::INTEGER, nullable: false, options: ['comment' => '他社ポイント利用額'])]
    private int $externalPoint;

    #[ORM\Column(type: Types::INTEGER, nullable: false, options: ['comment' => '代金調整額'])]
    private int $adjustment;

    #[ORM\ManyToOne(targetEntity: BadDebt::class, inversedBy: 'paymentInvoices')]
    #[ORM\JoinColumn(name: 'bad_debt_id', referencedColumnName: 'id', nullable: true, options: ['comment' => '貸倒ID'])]
    private ?BadDebt $badDebt;

    #[ORM\Column(type: Types::INTEGER, nullable: false, options: ['comment' => 'ショップID'])]
    private int $shopId;

    #[ORM\Column(type: Types::BOOLEAN, nullable: false, options: ['default' => true, 'comment' => '有効フラグ'])]
    private bool $isActive;

    #[ORM\Column(type: Types::STRING, length: 50, nullable: false, options: ['comment' => '作成プログラムID'])]
    private string $createdProgramId;

    #[ORM\Column(type: Types::STRING, length: 50, nullable: false, options: ['comment' => '更新プログラムID'])]
    private string $updatedProgramId;

    #[ORM\Column(type: Types::BIGINT, nullable: false, options: ['comment' => '作成ユーザID'])]
    private int $createdBy;

    #[ORM\Column(type: Types::BIGINT, nullable: false, options: ['comment' => '更新ユーザID'])]
    private int $updatedBy;

    #[ORM\Version, ORM\Column(type: Types::INTEGER)]
    private int $lockVersion;

    /**
     * @var Collection<int, Deposit>
     */
    #[ORM\OneToMany(targetEntity: Deposit::class, mappedBy: 'paymentInvoice')]
    private ?Collection $deposits;

    /**
     * @var Collection<int, TemporaryDepositAllocation>
     */
    #[ORM\OneToMany(targetEntity: TemporaryDepositAllocation::class, mappedBy: 'paymentInvoice')]
    private ?Collection $temporaryDepositAllocations;

    public function __construct(
        int $paymentInvoiceAmount,
        TypeDepositStatus $typeDepositStatus,
        int $shippingFee,
        int $codFee,
        int $offerDiscountAmount,
        int $coupon,
        int $externalCoupon,
        int $point,
        int $externalPoint,
        int $adjustment,
        ?BadDebt $badDebt,
        int $shopId,
        string $createdProgramId,
        string $updatedProgramId,
        int $createdBy,
        int $updatedBy,
        ?Customer $customer,
        ?Shipping $shipping,
        bool $isActive = true,
        int $lockVersion = 1,
    ) {
        $this->customer                      = $customer;
        $this->shipping                      = $shipping;
        $this->paymentInvoiceAmount          = $paymentInvoiceAmount;
        $this->typeDepositStatus             = $typeDepositStatus;
        $this->shippingFee                   = $shippingFee;
        $this->codFee                        = $codFee;
        $this->offerDiscountAmount           = $offerDiscountAmount;
        $this->coupon                        = $coupon;
        $this->externalCoupon                = $externalCoupon;
        $this->point                         = $point;
        $this->externalPoint                 = $externalPoint;
        $this->adjustment                    = $adjustment;
        $this->badDebt                       = $badDebt;
        $this->shopId                        = $shopId;
        $this->isActive                      = $isActive;
        $this->createdProgramId              = $createdProgramId;
        $this->updatedProgramId              = $updatedProgramId;
        $this->createdBy                     = $createdBy;
        $this->updatedBy                     = $updatedBy;
        $this->lockVersion                   = $lockVersion;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getPaymentInvoiceAmount(): int
    {
        return $this->paymentInvoiceAmount;
    }

    public function getTypeDepositStatus(): TypeDepositStatus
    {
        return $this->typeDepositStatus;
    }

    /**
     * @param Collection<int, Deposit> $deposits
     */
    public function setDeposits(Collection $deposits): static
    {
        $this->deposits = $deposits;

        return $this;
    }

    /**
     * @param Collection<int, TemporaryDepositAllocation> $temporaryDepositAllocations
     */
    public function setTemporaryDepositAllocations(Collection $temporaryDepositAllocations): void
    {
        $this->temporaryDepositAllocations = $temporaryDepositAllocations;
    }

    public function getShipping(): ?Shipping
    {
        return $this->shipping;
    }

    /**
     * @return null|Collection<int, Deposit>
     */
    public function getDeposits(): ?Collection
    {
        return $this->deposits;
    }

    public function getCustomer(): ?Customer
    {
        return $this->customer;
    }
}

