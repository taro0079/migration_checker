<?php

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

require './src/migration_checker.php';

class DbConnectorTest extends TestCase
{
    private mysqli&MockObject $mysqli;

    public function testGetDesc(): void
    {
        $targetClass = new DbConnector($this->mysqli);
        $result = $targetClass->getDesc('trn_payment_invoce');
        $expected = [
            new DbColumnDto('id', DbType::BIGINT, false, null, null, null),
            new DbColumnDto("customer_id", DbType::INT, true, null, null, null),
            new DbColumnDto("shipping_id", DbType::BIGINT, true, null, null, null),
            new DbColumnDto("payment_invoice_amount", DbType::INT, false, null, null, null),
            new DbColumnDto("type_deposit_status", DbType::VARCHAR, false, 1, null, null),
            new DbColumnDto("created_program_id", DbType::VARCHAR, false, 50, null, null),

        ];
        $customerId = array_filter($result, fn (DbColumnDto $column) => $column->field === 'customer_id');
        /**
         * @var DbColumnDto $tagetResult
         */
        $tagetResult = count($customerId) > 0 ? array_values($customerId)[0] : null;
        $this->assertSame($tagetResult->field, 'customer_id');
        $this->assertTrue($tagetResult->nullable);
        $this->assertNull($tagetResult->length);
        $this->assertSame(DbType::INT, $tagetResult->type);
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
