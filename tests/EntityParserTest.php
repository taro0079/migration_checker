<?php


use PHPUnit\Framework\TestCase;

require './src/migration_checker.php';

class EntityParserTest extends TestCase
{
    private string $file_path = './test.php';

    public function testGetTableName()
    {
        $targetClass = new EntityParser($this->file_path);
        $result = $targetClass->getTableName();
        $this->assertSame('trn_payment_invoice', $result);
    }

    public function testGetProperties(): void
    {
        $targetClass = new EntityParser($this->file_path);
        $result = $targetClass->getProperties();
        $this->assertCount(23, $result);
    }

    public function testCreateComplete(): void
    {
        $targetClass = new EntityParser($this->file_path);
        $result = $targetClass->createSets();
        $this->assertCount(26, $result);
    }

    public function testGetDbColumn(): void
    {
        $targetClass = new EntityParser($this->file_path);
        $result = $targetClass->getDbColumn();
        $result_column_names = array_map(fn (DbColumnDto $dto) => $dto->field, $result);
        $expected_column_names = [
            'id',
            'customer_id',
            'shipping_id',
            'payment_invoice_amount',
            'type_deposit_status',
            'shipping_fee',
            'cod_fee',
            'offer_discount_amount',
            'coupon',
            'external_coupon',
            'point',
            'external_point',
            'adjustment',
            'bad_debt_id',
            'shop_id',
            'is_active',
            'created_program_id',
            'updated_program_id',
            'created_by',
            'updated_by',
            'lock_version',
            'created_at',
            'updated_at',
        ];
        $this->assertCount(23, $result_column_names);
        $this->assertSame($expected_column_names, $result_column_names);

        $customerId = array_filter($result, fn (DbColumnDto $column) => $column->field === 'customer_id');
        $tagetResult = count($customerId) > 0 ? array_values($customerId)[0] : null;
        $this->assertSame($tagetResult->field, 'customer_id');
        $this->assertSame(DbType::RELATION, $tagetResult->type);
        $this->assertNull($tagetResult->length);
    }
}
