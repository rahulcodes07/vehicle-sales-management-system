<?php
declare(strict_types=1);

final class SaleService
{
    public function __construct(private mysqli $connection)
    {
    }

    /**
     * AddSale is the proposal-facing stored procedure and the only sale write path.
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
        $safePaymentMode = mysqli_real_escape_string($this->connection, $paymentMode);
        $query = sprintf(
            "CALL AddSale(%d, %d, %d, %.2f, '%s')",
            $vehicleId,
            $customerId,
            $employeeId,
            $salePrice,
            $safePaymentMode
        );
        $result = mysqli_query($this->connection, $query);
        $row = $result instanceof mysqli_result ? (mysqli_fetch_assoc($result) ?: []) : [];
        if ($result instanceof mysqli_result) {
            mysqli_free_result($result);
        }

        while (mysqli_more_results($this->connection)) {
            $extraResult = mysqli_next_result($this->connection);
            if ($extraResult instanceof mysqli_result) {
                mysqli_free_result($extraResult);
            }
        }

        return [
            'status_code' => (int) ($row['status_code'] ?? 500),
            'status_msg' => (string) ($row['status_message'] ?? 'The database returned no transaction status.'),
        ];
    }
}
