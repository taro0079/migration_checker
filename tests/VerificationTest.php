<?php

declare(strict_types=1);

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

require './src/migration_checker.php';

class VerificationTest extends TestCase
{
    private mysqli&MockObject $mysqli;
    private string $file_path = './test.php';
    private FileReader&MockObject $fileReaderMock;

    /**
     * @test
     */
    public function testReadFile()
    {
        /**
         * @var FileReader&MockObject $fileReaderMock
         */
        $result = $this->fileReaderMock->readFile($this->file_path);
        var_dump($result);
        $this->assertTrue(count($result) > 0);
    }

    public function testColumnCheck(): void
    {
        $entityParser = new EntityParser($this->fileReaderMock);

        // readFileメソッドが呼び出されたときに返す値を定義
        $dbConnector  = new DbConnector($this->mysqli);
        $verification = new Verification($dbConnector, $entityParser);
        $result       = $verification->columnCheck();
        $this->assertTrue($result);
    }

    protected function setUp(): void
    {
        $this->mysqli = $this->createMock(mysqli::class);
        /**
         * @var mysqli_result&MockObject
         */
        $msqli_result = $this->createMock(mysqli_result::class);
        $this->mysqli->method('query')

            ->willReturn($msqli_result);
        $msqli_result->method('fetch_all')
            ->willReturn(
                [
                    ['id', 'bigint', 'NO', 'PRI', null, 'auto_increment'],
                    ['customer_id', 'int', 'YES', 'MUL', null, ''],
                    ['payment_invoice_amount', 'int', 'NO', '', null, ''],
                    ['type_deposit_status', 'varchar(1)', 'NO', '', null, ''],
                    ['created_at', 'datetime', 'NO', '', null, ''],
                    ['updated_at', 'datetime', 'NO', '', null, ''],
                ]
            );
        $this->fileReaderMock = $this->getMockBuilder(FileReader::class)
            ->setConstructorArgs(['dummy'])
            ->onlyMethods(['readFile'])
            ->getMock();

        // readFileメソッドが呼び出されたときに返す値を定義
        $this->fileReaderMock->method('readFile')->willReturn([
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
        ]);
    }
}
