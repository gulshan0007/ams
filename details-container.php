<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Equipment Details</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            color: #333;
        }

        #details-container {
            background-color: #fff;
            width: 100%;
            max-width: 500px;
            padding: 20px;
            margin: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            text-align: center;
        }

        h2 {
            color: #333;
            margin-bottom: 10px;
        }

        .detail {
            display: flex;
            justify-content: space-between;
            padding: 10px;
            border-bottom: 1px solid #eaeaea;
        }

        .detail:last-child {
            border-bottom: none;
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
    <div id="details-container">
        <h2>Equipment Details</h2>
        <!-- Details will be displayed here -->
    </div>

    <script>
        // Function to fetch and display details by UID
        function fetchAndDisplayDetails(department, uid) {
            const apiUrl = `https://ugac.gymkhana.iitb.ac.in/~ams/api.php?department=${department}&uid=${uid}`;

            fetch(apiUrl)
                .then(response => response.json())
                .then(data => {
                    const container = document.getElementById('details-container');

                    if (data.error) {
                        container.innerText = data.error;
                    } else {
                        container.innerHTML = '<h2>Equipment Details</h2>'; // Reset and add title

                        // Create and populate details
                        for (const key in data) {
                            if (key !== 'photo') {
                                const detailDiv = document.createElement('div');
                                detailDiv.classList.add('detail');

                                const label = document.createElement('span');
                                label.classList.add('detail-label');
                                label.textContent = `${key}:`;

                                const value = document.createElement('span');
                                value.classList.add('detail-value');
                                value.textContent = data[key];

                                detailDiv.appendChild(label);
                                detailDiv.appendChild(value);
                                container.appendChild(detailDiv);
                            }
                        }

                        // Display image if there is a photo field
                        if (data.photo) {
                            const imageContainer = document.createElement('div');
                            imageContainer.classList.add('image-container');

                            const img = document.createElement('img');
                            img.src = data.photo; // Assuming 'photo' stores image URL/path
                            img.alt = 'Equipment Image';

                            imageContainer.appendChild(img);
                            container.appendChild(imageContainer);
                        }
                    }
                })
                .catch(error => {
                    console.error('Error fetching details:', error);
                    document.getElementById('details-container').innerText = "An error occurred.";
                });
        }

        // Call the function with the department and uid
        fetchAndDisplayDetails('civil', 1); // Replace 'civil' and '1' with dynamic values as needed
    </script>
</body>
</html>
