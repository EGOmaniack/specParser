<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>
    <form action="./ajax/takeDataNean.php" method="post"
        enctype="multipart/form-data">
        <input
            required
            multiple
            type="file"
            name="specs[]"
            id="specs"
        >
        <input type="submit" value="Отправить">
    </form>
</body>
</html>