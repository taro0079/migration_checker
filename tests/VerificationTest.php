<?php

declare(strict_types=1);

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

require './src/migration_checker.php';

class VerificationTest extends TestCase
{
    private mysqli&MockObject $mysqli;
    private string $file_path = './test.php';

    public function testColumnCheck(): void
    {
        $entityParser = new EntityParser($this->file_path);
        $dbConnector = new DbConnector($this->mysqli);
        $verification = new Verification($dbConnector, $entityParser);
        $result = $verification->columnCheck();
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
                    ["id","bigint","NO","PRI",null,"auto_increment"],
                    ["customer_id","int","YES","MUL",null,""],
                    ["shipping_id","bigint","YES","UNI",null,""],
                    ["payment_invoice_amount","int","NO","",null,""],
                    ["type_deposit_status","varchar(1)","NO","",null,""],
                    ["bad_debt_id","bigint","YES","MUL",null,""],
                    ["shipping_fee","int","NO","",null,""],
                    ["cod_fee","int","NO","",null,""],
                    ["offer_discount_amount","int","NO","",null,""],
                    ["coupon","int","NO","",null,""],
                    ["external_coupon","int","NO","",null,""],
                    ["point","int","NO","",null,""],
                    ["external_point","int","NO","",null,""],
                    ["adjustment","int","NO","",null,""],
                    ["shop_id","int","NO","",null,""],
                    ["is_active","tinyint(1)","NO","","1",""],
                    ["created_program_id","varchar(50)","NO","",null,""],
                    ["updated_program_id","varchar(50)","NO","",null,""],
                    ["created_by","bigint","NO","",null,""],
                    ["updated_by","bigint","NO","",null,""],
                    ["lock_version","int","NO","","1",""],
                    ["created_at","datetime","NO","",null,""],
                    ["updated_at","datetime","NO","",null,""]
                ]
            );
    }
}
