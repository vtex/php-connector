<html>

<head>
    <title>PHP Connector - Payment installments </title>
</head>

<body>
    <h2>Payment installments</h2>

    <form action="installments.php" method="POST">
        Please select the number of installments:
        <select name="installments" id="installments">
            <?php for ($i = 1; $i <= $numberOfInstallments; $i++) : ?>
                <option value="<?= $i; ?>"><?= $i; ?></option>
            <?php endfor; ?>
        </select>
        <input type="hidden" name="paymentId" value="<?= $paymentId; ?>">
        <input type="submit" value="Finish purchase">
    </form>
</body>

</html>