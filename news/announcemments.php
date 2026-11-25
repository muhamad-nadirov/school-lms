<?php

?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Объявления | School LMS</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            margin: 0;
            padding: 30px;
        }

        .container {
            max-width: 900px;
            margin: auto;
        }

        h1 {
            color: #263449;
        }

        .announcement {
            background: white;
            padding: 20px;
            margin-top: 20px;
            border-radius: 12px;
            box-shadow: 0 2px 8px #00000010;
        }

        .announcement h2 {
            color: #2563eb;
        }

        .date {
            color: #888;
            font-size: 13px;
        }
    </style>
</head>

<body>

<div class="container">
<div class="announcement">
    <h2>Создать объявление</h2>

    <form id="announcementForm">
        <input
            type="text"
            id="title"
            placeholder="Заголовок объявления"
            required
            style="width:100%;padding:12px;box-sizing:border-box;margin-bottom:12px;"
        >

        <textarea
            id="content"
            placeholder="Текст объявления..."
            rows="4"
            required
        ></textarea>

        <button type="submit">Опубликовать</button>
    </form>
</div>

<script>
document.getElementById("announcementForm")
.addEventListener("submit", function(event) {
    event.preventDefault();

    const title = document.getElementById("title").value;
    const content = document.getElementById("content").value;

    const announcement = document.createElement("div");
    announcement.className = "announcement";

    const heading = document.createElement("h2");
    heading.textContent = title;

    const paragraph = document.createElement("p");
    paragraph.textContent = content;

    announcement.appendChild(heading);
    announcement.appendChild(paragraph);

    document.querySelector(".container")
        .appendChild(announcement);

    this.reset();
});
</script>

    <h1>Объявления</h1>
    <p>Новости и важная информация для учеников.</p>

    <div class="announcement">
        <h2>Добро пожаловать в School LMS!</h2>
        <p>Здесь будут публиковаться важные объявления.</p>
        <div class="date">School Administration</div>
    </div>

</div>

</body>
</html>
