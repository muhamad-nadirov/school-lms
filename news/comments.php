<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Комментарии | School LMS</title>

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

        .lesson {
            background: white;
            padding: 22px;
            margin-top: 22px;
            border-radius: 12px;
            box-shadow: 0 2px 8px #00000010;
        }

        .comment {
            background: #f4f6f9;
            padding: 15px;
            margin-top: 12px;
            border-radius: 8px;
        }

        .author {
            font-weight: bold;
            color: #2563eb;
        }

        .date {
            color: #888;
            font-size: 12px;
        }

        textarea, input {
            width: 100%;
            box-sizing: border-box;
            padding: 12px;
            margin-top: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-family: Arial;
        }

        button {
            background: #2563eb;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 8px;
            margin-top: 10px;
            cursor: pointer;
        }

        button:hover {
            background: #1d4ed8;
        }

        .delete {
            background: #dc2626;
            padding: 7px 12px;
        }
    </style>
</head>

<body>

<div class="container">

    <h1>Комментарии</h1>
    <p>Обсуждение уроков и заданий.</p>

    <div class="lesson">
        <h2>Урок: Основы PHP</h2>

        <div id="lessonComments"></div>

        <input type="text" id="lessonName"
               placeholder="Ваше имя">

        <textarea id="lessonText"
                  placeholder="Напишите комментарий..."
                  rows="3"></textarea>

        <button onclick="addComment('lesson')">
            Отправить комментарий
        </button>
    </div>

    <div class="lesson">
        <h2>Задание: Практика PHP</h2>

        <div id="taskComments"></div>

        <input type="text" id="taskName"
               placeholder="Ваше имя">

        <textarea id="taskText"
                  placeholder="Напишите комментарий..."
                  rows="3"></textarea>

        <button onclick="addComment('task')">
            Отправить комментарий
        </button>
    </div>

</div>

<script>
function addComment(type) {

    const nameInput = document.getElementById(
        type === 'lesson' ? 'lessonName' : 'taskName'
    );

    const textInput = document.getElementById(
        type === 'lesson' ? 'lessonText' : 'taskText'
    );

    const name = nameInput.value.trim();
    const text = textInput.value.trim();

    if (name === '' || text === '') {
        alert('Заполните имя и комментарий!');
        return;
    }

    const comment = document.createElement('div');
    comment.className = 'comment';

    const author = document.createElement('p');
    author.className = 'author';
    author.textContent = name;

    const message = document.createElement('p');
    message.textContent = text;

    const date = document.createElement('p');
    date.className = 'date';
    date.textContent = new Date().toLocaleString('ru-RU');

    const deleteButton = document.createElement('button');
    deleteButton.className = 'delete';
    deleteButton.textContent = 'Удалить';

    deleteButton.onclick = function() {
        comment.remove();
    };

    comment.append(author, message, date, deleteButton);

    const container = document.getElementById(
        type === 'lesson' ? 'lessonComments' : 'taskComments'
    );

    container.appendChild(comment);

    nameInput.value = '';
    textInput.value = '';
}
</script>

</body>
</html>
