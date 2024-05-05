function clearInputs() {
    document.getElementById('loginUsername').value = '';
    document.getElementById('loginPassword').value = '';
    document.getElementById('studentId').value = '';
    document.getElementById('marks').value = '';
}

function login() {
    const username = document.getElementById('loginUsername').value;
    const password = document.getElementById('loginPassword').value;
    fetch('process.php', {
        method: 'POST',
        body: JSON.stringify({ action: 'login', username: username, password: password }),
        headers: {
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.text())
    .then(data => {
        alert(data.split('|')[0]);
        if (data.includes("successful")) {
            document.getElementById('logoutBtn').style.display = 'block';
            document.getElementById('loginPanel').style.display = 'none';
            if (data.includes("|teacher")) {
                document.getElementById('teacherPanel').style.display = 'block';
            } else if (data.includes("|student")) {
                document.getElementById('studentPanel').style.display = 'block';
                fetchMarks();
            }
        }
        clearInputs();
    });
}

function logout() {
    fetch('process.php', {
        method: 'POST',
        body: JSON.stringify({ action: 'logout' }),
        headers: {
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.text())
    .then(data => {
        alert(data);
        document.getElementById('logoutBtn').style.display = 'none';
        document.getElementById('loginPanel').style.display = 'block';
        document.getElementById('teacherPanel').style.display = 'none';
        document.getElementById('studentPanel').style.display = 'none';
        clearInputs();
    });
}

function fetchMarks() {
    fetch('process.php', {
        method: 'POST',
        body: JSON.stringify({ action: 'fetchMarks' }),
        headers: {
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.marks !== null) {
            const safeMarks = escapeHTML(data.marks.toString());
            document.getElementById('studentMarks').innerHTML = 'Marks: ' + safeMarks;
        } else {
            document.getElementById('studentMarks').innerHTML = 'No marks available.';
        }
    });
}

function escapeHTML(str) {
    return str.replace(/[&<>'"]/g, function(tag) {
        return ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            "'": '&#39;',
            '"': '&quot;'
        }[tag] || tag);
    });
}
