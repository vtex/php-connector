<html>

<head>
    <title>PHP Connector - Payment installments </title>
</head>

<body>
    <h2>Payment installments</h2>
    <form action="" method="POST">
        Please select the number of installments:
        <select name="installments" id="installments">
            <?php for ($i = 1; $i <= 24; $i++) : ?>
                <option value="<?= $i; ?>"><?= $i; ?></option>
                <tr>
                    <td><?= $key; ?></td>
                </tr>
            <?php endfor; ?>
        </select>
        <input type="submit" value="submit">
    </form>
</body>

</html>