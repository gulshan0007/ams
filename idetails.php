<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fetch Equipment Details</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px;
            color: #333;
        }

        #selection-container, #details-container {
            background-color: #fff;
            width: 100%;
            max-width: 600px;
            padding: 20px;
            margin: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            text-align: center;
        }

        h2 {
            margin-bottom: 10px;
        }

        select, input[type="number"] {
            padding: 10px;
            width: 100%;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 1em;
        }

        button {
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1em;
        }

        button:hover {
            background-color: #45a049;
        }

        .detail {
            display: flex;
            justify-content: space-between;
            padding: 10px;
            border-bottom: 1px solid #eaeaea;
        }

        .detail-label {
            font-weight: bold;
            color: #666;
        }

        .detail-value {
            color: #333;
        }

        .image-container {
            margin-top: 20px;
        }

        img {
            max-width: 100%;
            height: auto;
            border-radius: 5px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>

    <div id="selection-container">
        <h2>Select Department and UID</h2>
        <select id="department-select">
            <option value="">Select Department</option>
            <option value="civil">Civil</option>
            <option value="cse">CSE</option>
            <option value="mechanical">Mechanical</option>
            <!-- Add more departments as needed -->
        </select>

        <input type="number" id="uid-input" placeholder="Enter UID" min="1" required />

        <button onclick="fetchDetails()">Fetch Details</button>
    </div>

    <div id="details-container" style="display: none;">
        <h2>Equipment Details</h2>
        <!-- Details will be displayed here -->
    </div>

    <script>
        function fetchDetails() {
            const department = document.getElementById('department-select').value;
            const uid = document.getElementById('uid-input').value;

            if (!department || !uid) {
                alert("Please select a department and enter a valid UID.");
                return;
            }

            // Fetch data from the API
            fetch(`https://ugac.gymkhana.iitb.ac.in/~ams/recordDetails.php?department=${department}&uid=${uid}`)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        alert(data.error);
                    } else {
                        displayDetails(data);
                    }
                })
                .catch(error => {
                    console.error("Error fetching data:", error);
                    alert("An error occurred while fetching data.");
                });
        }

        function displayDetails(data) {
            const container = document.getElementById('details-container');
            container.innerHTML = '<h2>Equipment Details</h2>'; // Reset content
            container.style.display = 'block';

            // Create and populate details
            for (const key in data) {
                if (key === 'photo') {
                    // Create image element for the photo
                    const imageContainer = document.createElement('div');
                    imageContainer.classList.add('image-container');

                    const img = document.createElement('img');
                    img.src = data[key];  // Assumes the 'photo' field is a valid image path
                    img.alt = 'Equipment Image';

                    imageContainer.appendChild(img);
                    container.appendChild(imageContainer);
                } else {
                    // Create a detail row for each other field
                    const detailDiv = document.createElement('div');
                    detailDiv.classList.add('detail');

                    const label = document.createElement('span');
                    label.classList.add('detail-label');
                    label.textContent = `${key.replace(/_/g, ' ')}:`;  // Replace underscores with spaces

                    const value = document.createElement('span');
                    value.classList.add('detail-value');
                    value.textContent = data[key];

                    detailDiv.appendChild(label);
                    detailDiv.appendChild(value);
                    container.appendChild(detailDiv);
                }
            }
        }
    </script>
</body>
</html>
