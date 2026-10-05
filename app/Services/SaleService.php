<?php
declare(strict_types=1);

final class SaleService
{
    public function __construct(private mysqli $connection)
    {
    }

    /**
     * The stored procedure is intentionally the only sale write path.
     * Vehicle locking, the transaction, status change, and audit trigger all
     * belong to the database layer.
     *
     * @return array{status_code: int, status_msg: string}
     */
    public function record(
        int $vehicleId,
        int $customerId,
        int $employeeId,
        float $salePrice,
        string $paymentMode
    ): array {
        $statement = mysqli_prepare(
            $this->connection,
            'CALL sp_RecordSale(?, ?, ?, ?, ?, @status_code, @status_msg)'
        );
        mysqli_stmt_bind_param(
            $statement,
            'iiids',
            $vehicleId,
            $customerId,
            $employeeId,
            $salePrice,
            $paymentMode
        );
        mysqli_stmt_execute($statement);
        mysqli_stmt_close($statement);

        $result = mysqli_query(
            $this->connection,
            'SELECT @status_code AS status_code, @status_msg AS status_msg'
        );
        $row = mysqli_fetch_assoc($result) ?: [];

        return [
            'status_code' => (int) ($row['status_code'] ?? 500),
            'status_msg' => (string) ($row['status_msg'] ?? 'The database returned no transaction status.'),
        ];
    }
}
