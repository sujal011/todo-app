document.getElementById('addTaskForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const input = this.querySelector('input[name="task"]');
    const task = input.value.trim();
    if (task) {
        addTask(task);
        input.value = '';
    }
});

function addTask(task) {
    fetch('index.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `ajax=1&task=${encodeURIComponent(task)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const li = document.createElement('li');
            li.innerHTML = `
                <span class="task-text">${task}</span>
                <button class="delete-btn" data-id="${data.id}">Delete</button>
            `;
            document.getElementById('taskList').prepend(li);
            addDeleteListener(li.querySelector('.delete-btn'));
            showMessage('Task added successfully.', 'success');
        } else {
            showMessage(data.message, 'error');
        }
    })
    .catch(error => {
        showMessage('Error adding task. Please try again.', 'error');
    });
}

function deleteTask(button) {
    const id = button.dataset.id;
    fetch('index.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `ajax=1&delete=${id}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            button.closest('li').remove();
            showMessage('Task deleted successfully.', 'success');
        } else {
            showMessage(data.message, 'error');
        }
    })
    .catch(error => {
        showMessage('Error deleting task. Please try again.', 'error');
    });
}

function addDeleteListener(button) {
    button.addEventListener('click', function() {
        deleteTask(this);
    });
}

function showMessage(message, type) {
    const messageDiv = document.getElementById('message');
    messageDiv.textContent = message;
    messageDiv.className = type === 'error' ? 'error-message' : 'success-message';
    setTimeout(() => {
        messageDiv.textContent = '';
        messageDiv.className = '';
    }, 3000);
}

// Add delete listeners to existing buttons
document.querySelectorAll('.delete-btn').forEach(addDeleteListener);