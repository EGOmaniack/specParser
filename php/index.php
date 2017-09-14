<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>
    <div>Вариант 1</div>
    <form action="./ajax/takeData.php" method="post"
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
    <div>Вариант 2</div>
    <form action="./ajax/takeData2.php" method="post"
          enctype="multipart/form-data">
        <input type="text" name="rootSpec">
        <input
                required
                multiple
                type="file"
                name="specs[]"
                id="specs"
        >
        <input type="submit" value="Отправить">
    </form>
    <script src="script/main.js"></script>
</body>
</html>