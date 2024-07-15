<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

require './src/migration_checker.php';

class VerificationTest extends TestCase
{
    private FileReader&MockObject $fileReaderMock;

    public static function dataProvide(): array
    {
        $correctDbColumns = [
            ['id', 'bigint', 'NO', 'PRI', null, 'auto_increment'],
            ['customer_id', 'int', 'YES', 'MUL', null, ''],
            ['payment_invoice_amount', 'int', 'NO', '', null, ''],
            ['type_deposit_status', 'varchar(1)', 'NO', '', null, ''],
            ['created_at', 'datetime', 'NO', '', null, ''],
            ['updated_at', 'datetime', 'NO', '', null, ''],
        ];
        $collectEntity = [
            '#[ORM\Entity]',
            '#[ORM\Table(name: "trn_order", options: ["comment" => "受注"])]',
            'class Order',
            '{',
            '#[ORM\Column(type: Types::BIGINT, nullable: false, options: ["comment" => "請求ID"])]',
            '#[ORM\Id,',
            '   ORM\GeneratedValue()]',
            'private int $id;',
            '#[ORM\ManyToOne(targetEntity: Customer::class, inversedBy: "paymentInvoices")]',
            '#[ORM\JoinColumn(name: "customer_id", referencedColumnName: "id", nullable: true, options: ["comment" => "顧客ID"])]',
            'private ?Customer $customer;',
            '#[ORM\Column(type: Types::INTEGER, nullable: false, options: ["comment" => "請求金額"])]',
            'private int $paymentInvoiceAmount;',
            '#[ORM\Column(type: Types::STRING, nullable: false, enumType: TypeDepositStatus::class, options: ["comment" => "入金ステータス区分"])]',
            'private TypeDepositStatus $typeDepositStatus;',
        ];
        $wrongDbColumns = [
            ['id', 'bigint', 'NO', 'PRI', null, 'auto_increment'],
            ['customer_id', 'int', 'YES', 'MUL', null, ''],
            ['payment_invoice_amount', 'int', 'NO', '', null, ''],
            ['created_at', 'datetime', 'NO', '', null, ''],
            ['updated_at', 'datetime', 'NO', '', null, ''],
        ];
        $wrongEntity = [
            '#[ORM\Entity]',
            '#[ORM\Table(name: "trn_order", options: ["comment" => "受注"])]',
            'class Order',
            '{',
            '#[ORM\Column(type: Types::BIGINT, nullable: false, options: ["comment" => "請求ID"])]',
            '#[ORM\Id,',
            '   ORM\GeneratedValue()]',
            'private int $id;',
            '#[ORM\ManyToOne(targetEntity: Customer::class, inversedBy: "paymentInvoices")]',
            '#[ORM\JoinColumn(name: "customer_id", referencedColumnName: "id", nullable: true, options: ["comment" => "顧客ID"])]',
            'private ?Customer $customer;',
            '#[ORM\Column(type: Types::INTEGER, nullable: false, options: ["comment" => "請求金額"])]',
            'private int $paymentInvoiceAmount;',
        ];
        $typeWrongEntity = [
            '#[ORM\Entity]',
            '#[ORM\Table(name: "trn_order", options: ["comment" => "受注"])]',
            'class Order',
            '{',
            '#[ORM\Column(type: Types::BIGINT, nullable: false, options: ["comment" => "請求ID"])]',
            '#[ORM\Id,',
            '   ORM\GeneratedValue()]',
            'private int $id;',
            '#[ORM\ManyToOne(targetEntity: Customer::class, inversedBy: "paymentInvoices")]',
            '#[ORM\JoinColumn(name: "customer_id", referencedColumnName: "id", nullable: true, options: ["comment" => "顧客ID"])]',
            'private ?Customer $customer;',
            '#[ORM\Column(type: Types::INTEGER, nullable: false, options: ["comment" => "請求金額"])]',
            'private int $paymentInvoiceAmount;',
            '#[ORM\Column(type: Types::INTEGER, nullable: false, enumType: TypeDepositStatus::class, options: ["comment" => "入金ステータス区分"])]',
            'private TypeDepositStatus $typeDepositStatus;',
        ];

        return [
            [$correctDbColumns, $collectEntity, 0],
            [$wrongDbColumns, $collectEntity, 1],
            [$correctDbColumns, $wrongEntity, 1],
            [$correctDbColumns, $typeWrongEntity, 1],
        ];
    }

    /**
     * @dataProvider dataProvide
     */
    public function testColumnCheck(array $dbColumn, array $entityColumn, int $arrayCount): void
    {
        /**
         * @var mysqli&MockObject $mysqli
         */
        $mysqli = $this->createMock(mysqli::class);

        /**
         * @var mysqli_result&MockObject $msqli_result
         */
        $msqli_result = $this->createMock(mysqli_result::class);
        $mysqli->method('query')

            ->willReturn($msqli_result);
        $msqli_result->method('fetch_all')
            ->willReturn($dbColumn);
        /**
         * @var FileReader&MockObject $fileReaderMock
         */
        $fileReaderMock = $this->getMockBuilder(FileReader::class)
            ->setConstructorArgs(['dummy'])
            ->onlyMethods(['readFile'])
            ->getMock();

        // readFileメソッドが呼び出されたときに返す値を定義
        $fileReaderMock->method('readFile')->willReturn($entityColumn);
        $entityParser = new EntityParser($fileReaderMock);

        // readFileメソッドが呼び出されたときに返す値を定義
        $dbConnector  = new DbConnector($mysqli);
        $verification = new Verification($dbConnector, $entityParser);
        $result       = $verification->columnCheck();
        $this->assertCount($arrayCount, $result);
    }

    protected function setUp(): void
    {
    }
}
