<?php
session_start();
include 'connections.php';

if (!isset($_SESSION['username']) || !isset($_SESSION['department'])) {
    header("Location: login.php");
    exit();
}

$department = $_SESSION['department'];
$id = $_GET['id'] ?? null;

if (!$id) {
    header("Location: dashboard.php");
    exit();
}

// Fetch equipment details
$query = "SELECT * FROM $department WHERE id = ?";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$equipment = $result->fetch_assoc();

if (!$equipment) {
    header("Location: dashboard.php");
    exit();
}

// Fetch booking history
$booking_query = "SELECT * FROM bookings WHERE instrument_id = ? AND department = ? ORDER BY start_datetime DESC";
$booking_stmt = $mysqli->prepare($booking_query);
$booking_stmt->bind_param("is", $id, $department);
$booking_stmt->execute();
$booking_result = $booking_stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Equipment Details</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

    <style>
        :root {
            --primary-color: #2563eb;
            --secondary-color: #1e40af;
            --success-color: #16a34a;
            --danger-color: #dc2626;
            --text-primary: #1f2937;
            --text-secondary: #4b5563;
            --bg-primary: #f3f4f6;
            --bg-secondary: #ffffff;
            --border-color: #e5e7eb;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-primary);
            color: var(--text-primary);
            line-height: 1.6;
            padding: 2rem;
        }

        .download-button {
            background-color: var(--success-color);
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            margin: 1rem 1.5rem;
            transition: background-color 0.3s;
        }

        .download-button:hover {
            background-color: #15803d;
        }

        .booking-history-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.5rem;
            border-bottom: 1px solid var(--border-color);
        }

        .booking-history-header h2 {
            margin: 0;
            padding: 0;
            font-size: 1.25rem;
            font-weight: 600;
        }

        /* Add styles for PDF generation */
        @media print {
            .booking-history {
                break-inside: avoid;
            }
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background-color: var(--bg-secondary);
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            overflow: hidden;
        }

        .header {
            background-color: var(--primary-color);
            color: white;
            padding: 1.5rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h1 {
            font-size: 1.5rem;
            font-weight: 600;
        }

        .back-button {
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
            padding: 0.75rem 1.5rem;
            text-decoration: none;
            border-radius: 6px;
            transition: background-color 0.3s;
            font-weight: 500;
        }

        .back-button:hover {
            background-color: rgba(255, 255, 255, 0.2);
        }

        .content {
            padding: 2rem;
        }

        .detail-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .detail-item {
            background-color: #fff;
            border: 1px solid var(--border-color);
            padding: 1.25rem;
            border-radius: 8px;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .detail-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .detail-item h3 {
            color: var(--text-primary);
            font-size: 0.875rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 0.5rem;
        }

        .detail-value {
            color: var(--text-secondary);
            font-size: 1rem;
        }

        .equipment-image {
            max-width: 20%;
            height: auto;
            border-radius: 8px;
            margin: 1rem 0;
        }

        .status-badge {
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: 9999px;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .status-available {
            background-color: rgba(22, 163, 74, 0.1);
            color: var(--success-color);
        }

        .status-unavailable {
            background-color: rgba(220, 38, 38, 0.1);
            color: var(--danger-color);
        }

        .booking-history {
            margin-top: 2rem;
            background-color: white;
            border-radius: 8px;
            border: 1px solid var(--border-color);
            overflow: hidden;
        }

        .booking-history h2 {
            padding: 1.5rem;
            font-size: 1.25rem;
            font-weight: 600;
            border-bottom: 1px solid var(--border-color);
        }

        .booking-history table {
            width: 100%;
            border-collapse: collapse;
        }

        .booking-history th {
            background-color: var(--bg-primary);
            font-weight: 500;
            text-align: left;
            padding: 1rem 1.5rem;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .booking-history td {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid var(--border-color);
        }

        .booking-history tr:last-child td {
            border-bottom: none;
        }

        .booking-history tr:hover td {
            background-color: var(--bg-primary);
        }

        .description-box {
            white-space: pre-line;
            line-height: 1.7;
        }

        @media (max-width: 768px) {
            body {
                padding: 1rem;
            }

            .detail-grid {
                grid-template-columns: 1fr;
            }

            .header {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }

            .booking-history td, 
            .booking-history th {
                padding: 0.75rem;
            }
        }
    </style>
    <script>
        function downloadPDF() {
            // Get the equipment name for the PDF title
            const equipmentName = document.querySelector('.detail-value').textContent.trim();
            
            // Create a clone of the booking history table
            const element = document.querySelector('.booking-history').cloneNode(true);
            
            // Remove the download button from the clone
            const downloadBtn = element.querySelector('.download-button');
            if (downloadBtn) {
                downloadBtn.remove();
            }

            // Configure PDF options
            const opt = {
                margin: 1,
                filename: `${equipmentName}_booking_history.pdf`,
                image: { type: 'jpeg', quality: 0.98 },
                html2canvas: { scale: 2 },
                jsPDF: { unit: 'in', format: 'a4', orientation: 'portrait' }
            };

            // Generate PDF
            html2pdf().set(opt).from(element).save();
        }
    </script>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Equipment Details</h1>
            <a href="dashboard.php" class="back-button">← Back to Dashboard</a>
        </div>

        <div class="content">
            <?php if ($equipment['photo']): ?>
            <div class="detail-item">
                <h3>Equipment Photo</h3>
                <img src="<?php echo htmlspecialchars($equipment['photo']); ?>" alt="Equipment Photo" class="equipment-image">
            </div>
            <?php endif; ?>

            <div class="detail-grid">
                <div class="detail-item">
                    <h3>Equipment Name</h3>
                    <div class="detail-value"><?php echo htmlspecialchars($equipment['equipment_name']); ?></div>
                </div>
                <div class="detail-item">
                    <h3>Department</h3>
                    <div class="detail-value"><?php echo htmlspecialchars($equipment['equipment_dept']); ?></div>
                </div>
                <div class="detail-item">
                    <h3>Status</h3>
                    <div class="detail-value">
                        <span class="status-badge <?php echo strtolower($equipment['availability']) === 'available' ? 'status-available' : 'status-unavailable'; ?>">
                            <?php echo htmlspecialchars($equipment['availability']); ?>
                        </span>
                    </div>
                </div>
                <div class="detail-item">
                    <h3>Current User</h3>
                    <div class="detail-value"><?php echo $equipment['currently_used_by'] ? htmlspecialchars($equipment['currently_used_by']) : 'N/A'; ?></div>
                </div>
                <div class="detail-item">
                    <h3>Last Used By</h3>
                    <div class="detail-value"><?php echo $equipment['last_used_by'] ? htmlspecialchars($equipment['last_used_by']) : 'N/A'; ?></div>
                </div>
                <div class="detail-item">
                    <h3>Year of Purchase</h3>
                    <div class="detail-value"><?php echo htmlspecialchars($equipment['year_of_purchase']); ?></div>
                </div>
                <div class="detail-item">
                    <h3>MMD No</h3>
                    <div class="detail-value"><?php echo htmlspecialchars($equipment['mmd_no']); ?></div>
                </div>
                <div class="detail-item">
                    <h3>Supplier</h3>
                    <div class="detail-value"><?php echo htmlspecialchars($equipment['supplier']); ?></div>
                </div>
                <div class="detail-item">
                    <h3>Amount</h3>
                    <div class="detail-value">₹<?php echo number_format($equipment['amount'], 2); ?></div>
                </div>
                <div class="detail-item">
                    <h3>Fund</h3>
                    <div class="detail-value"><?php echo htmlspecialchars($equipment['fund']); ?></div>
                </div>
                <div class="detail-item">
                    <h3>Incharge</h3>
                    <div class="detail-value"><?php echo htmlspecialchars($equipment['incharge']); ?></div>
                </div>
            </div>

            <div class="detail-item">
                <h3>Specification</h3>
                <div class="detail-value description-box"><?php echo nl2br(htmlspecialchars($equipment['specification'])); ?></div>
            </div>

            <div class="detail-item">
                <h3>Description</h3>
                <div class="detail-value description-box"><?php echo nl2br(htmlspecialchars($equipment['description'])); ?></div>
            </div>

            <div class="detail-item">
                <h3>Purpose</h3>
                <div class="detail-value description-box"><?php echo nl2br(htmlspecialchars($equipment['purpose'])); ?></div>
            </div>

            <div class="detail-item">
                <h3>Users</h3>
                <div class="detail-value description-box"><?php echo nl2br(htmlspecialchars($equipment['users'])); ?></div>
            </div>

            <div class="booking-history">
                <h2>Booking History</h2>
                <table>
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Start Date/Time</th>
                            <th>End Date/Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($booking = $booking_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($booking['username']); ?></td>
                            <td><?php echo date('M j, Y g:i A', strtotime($booking['start_datetime'])); ?></td>
                            <td><?php echo date('M j, Y g:i A', strtotime($booking['end_datetime'])); ?></td>
                        </tr>
                        <?php endwhile; ?>
                        <?php if ($booking_result->num_rows === 0): ?>
                        <tr>
                            <td colspan="3" style="text-align: center;">No booking history available</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <!-- <div class="booking-history">
                <div class="booking-history-header">
                    <h2>Booking History</h2>
                    <button onclick="downloadPDF()" class="download-button">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                            <polyline points="7 10 12 15 17 10"/>
                            <line x1="12" y1="15" x2="12" y2="3"/>
                        </svg>
                        Download Report
                    </button>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Start Date/Time</th>
                            <th>End Date/Time</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php while ($booking = $booking_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($booking['username']); ?></td>
                            <td><?php echo date('M j, Y g:i A', strtotime($booking['start_datetime'])); ?></td>
                            <td><?php echo date('M j, Y g:i A', strtotime($booking['end_datetime'])); ?></td>
                        </tr>
                        <?php endwhile; ?>
                        <?php if ($booking_result->num_rows === 0): ?>
                        <tr>
                            <td colspan="3" style="text-align: center;">No booking history available</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div> -->
        </div>
    </div>
        </div>
    </div>
</body>
</html>