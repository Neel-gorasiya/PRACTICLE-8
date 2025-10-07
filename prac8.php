<?php
// database configuration
$host = "localhost";      
$user = "root";           
$pass = "";               
$db   = "studenthub";     

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Database Connection failed: " . htmlspecialchars($conn->connect_error));
}

$msg = "";
$editStudent = null;

// insert
if (isset($_POST['add_student'])) {
    $stmt = $conn->prepare("INSERT INTO students (student_id, name, email, course, year) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("isssi", $_POST['student_id'], $_POST['name'], $_POST['email'], $_POST['course'], $_POST['year']);
    if ($stmt->execute()) $msg = "🎉 Student added successfully!";
    else $msg = "❌ Error: " . htmlspecialchars($stmt->error);
    $stmt->close();
}

// update
if (isset($_POST['update_student'])) {
    $stmt = $conn->prepare("UPDATE students SET name=?, email=?, course=?, year=? WHERE student_id=?");
    $stmt->bind_param("sssii", $_POST['name'], $_POST['email'], $_POST['course'], $_POST['year'], $_POST['student_id']);
    if ($stmt->execute()) $msg = "✅ Student updated successfully!";
    else $msg = "❌ Error: " . htmlspecialchars($stmt->error);
    $stmt->close();
}

// delete
if (isset($_POST['delete_student'])) {
    $stmt = $conn->prepare("DELETE FROM students WHERE student_id=?");
    $stmt->bind_param("i", $_POST['student_id']);
    if ($stmt->execute()) $msg = "🗑️ Student deleted successfully!";
    else $msg = "❌ Error: " . htmlspecialchars($stmt->error);
    $stmt->close();
}

// load student for edit 
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $res = $conn->prepare("SELECT * FROM students WHERE student_id=?");
    $res->bind_param("i", $id);
    $res->execute();
    $editStudent = $res->get_result()->fetch_assoc();
    $res->close();
}

// fetch all students
$students = $conn->query("SELECT * FROM students ORDER BY student_id ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>StudentHub Portal</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body { 
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #36d1dc 0%, #5b86e5 100%);
            min-height: 100vh;
            padding: 20px;
            overflow-x: hidden;
        }

        .container {
            max-width: 1350px;
            margin: 0 auto;
            background: #ffffffee;
            border-radius: 20px;
            box-shadow: 0 15px 45px rgba(0, 0, 0, 0.15);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #6dd5ed 100%);
            color: white;
            padding: 40px;
            text-align: center;
        }

        .header h1 {
            font-size: 3em;
            font-weight: 700;
            margin-bottom: 10px;
            letter-spacing: -1px;
        }

        .header p {
            font-size: 1.1em;
            font-weight: 300;
            opacity: 0.95;
        }

        .content {
            padding: 40px;
        }

        .form-section {
            background: #f8f9fa;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 35px;
            border: 1px solid #e3e3e3;
            box-shadow: 0 8px 20px rgba(0,0,0,0.05);
        }

        .form-section h2 {
            font-size: 1.8em;
            font-weight: 600;
            color: #2d3436;
            margin-bottom: 20px;
        }

        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }

        input[type="text"], input[type="email"], input[type="number"] {
            width: 100%;
            padding: 12px 18px;
            border: 2px solid #ccc;
            border-radius: 10px;
            font-size: 15px;
            background-color: #fff;
            transition: 0.3s ease;
        }

        input:focus {
            border-color: #5b86e5;
            box-shadow: 0 0 10px rgba(91,134,229,0.3);
            outline: none;
        }

        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: 30px;
            cursor: pointer;
            font-weight: 600;
            text-transform: uppercase;
            transition: all 0.3s ease;
            margin: 5px;
        }

        .btn-primary { background: #667eea; color: white; }
        .btn-success { background: #2ecc71; color: white; }
        .btn-danger  { background: #e74c3c; color: white; }
        .btn-secondary { background: #95a5a6; color: white; }

        .btn:hover {
            transform: translateY(-2px);
            opacity: 0.9;
        }

        .msg {
            background: linear-gradient(135deg, #56ab2f, #a8e063);
            color: white;
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            box-shadow: 0 5px 15px rgba(86,171,47,0.2);
            animation: fadeIn 0.4s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        table {
            width: 100%;
            border-collapse: collapse;
            border-radius: 10px;
            overflow: hidden;
            background: white;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }

        th, td {
            padding: 15px;
            text-align: center;
        }

        th {
            background: #5b86e5;
            color: white;
            text-transform: uppercase;
            font-size: 14px;
        }

        tr:nth-child(even) {
            background: #f2f2f2;
        }

        tr:hover {
            background: #e6f0ff;
        }

        .action-buttons {
            display: flex;
            justify-content: center;
            gap: 10px;
        }

        @media (max-width: 768px) {
            .container { margin: 10px; }
            .form-row { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎓 Student Management System</h1>
            <p><strong>Created by Neel Gorasiya - 24CS030</strong></p>
        </div>

        <div class="content">
            <?php if (!empty($msg)) echo "<div class='msg'>$msg</div>"; ?>

            <div class="form-section">
                <h2><?= $editStudent ? "📝 Edit Student (ID: {$editStudent['student_id']})" : "➕ Add New Student" ?></h2>
                <form method="POST">
                    <div class="form-row">
                        <div>
                            <input type="number" name="student_id" placeholder="🆔 Student ID" value="<?= $editStudent['student_id'] ?? '' ?>" <?= $editStudent ? 'readonly' : 'required' ?>>
                        </div>
                        <div>
                            <input type="text" name="name" placeholder="👤 Full Name" value="<?= $editStudent['name'] ?? '' ?>" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div>
                            <input type="email" name="email" placeholder="📧 Email" value="<?= $editStudent['email'] ?? '' ?>" required>
                        </div>
                        <div>
                            <input type="text" name="course" placeholder="📘 Course" value="<?= $editStudent['course'] ?? '' ?>" required>
                        </div>
                        <div>
                            <input type="number" name="year" placeholder="📅 Year" value="<?= $editStudent['year'] ?? '' ?>" min="1" max="4" required>
                        </div>
                    </div>

                    <?php if ($editStudent): ?>
                        <button type="submit" name="update_student" class="btn btn-success">Update</button>
                        <a href="studenthub.php" class="btn btn-secondary">Cancel</a>
                    <?php else: ?>
                        <button type="submit" name="add_student" class="btn btn-primary">Add</button>
                    <?php endif; ?>
                </form>
            </div>

            <div class="form-section">
                <h2>📋 All Students</h2>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Course</th>
                            <th>Year</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $students->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['student_id']) ?></td>
                            <td><?= htmlspecialchars($row['name']) ?></td>
                            <td><?= htmlspecialchars($row['email']) ?></td>
                            <td><?= htmlspecialchars($row['course']) ?></td>
                            <td><?= htmlspecialchars($row['year']) ?></td>
                            <td>
                                <div class="action-buttons">
                                    <form method="GET" style="display:inline;">
                                        <input type="hidden" name="edit" value="<?= $row['student_id'] ?>">
                                        <button type="submit" class="btn btn-primary">✏ Edit</button>
                                    </form>
                                    <form method="POST" style="display:inline;" onsubmit="return confirm('⚠ Are you sure you want to delete this student?')">
                                        <input type="hidden" name="student_id" value="<?= $row['student_id'] ?>">
                                        <button type="submit" name="delete_student" class="btn btn-danger">🗑 Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
<?php $conn->close(); ?>
