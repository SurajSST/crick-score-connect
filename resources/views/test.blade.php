<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Game Data</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

</head>
<body>
    <h1>Update Game Data</h1>

    <!-- Define the form -->
    <form id="updateGameDataForm">
        <!-- Hidden input fields for data attributes -->
        <input type="hidden" name="match_id" value="11">
        <input type="hidden" name="isGameFinished" value="false">
        <input type="hidden" name="isGameCanceled" value="false">
        <input type="hidden" name="target" value="0.0">
        <input type="hidden" name="CRR" value="18.00">
        <input type="hidden" name="RRR" value="0.00">

        <!-- Submit button -->
        <button type="button" onclick="submitForm()">Update Game Data</button>
    </form>

    <script>
        function submitForm() {
            const form = document.getElementById('updateGameDataForm');
            const formData = new FormData(form);

            // Convert form data to JSON object
            const requestData = {};
            formData.forEach((value, key) => {
                requestData[key] = value;
            });

            // Make an HTTP POST request to your Laravel route
            fetch('api/update-game-data', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(requestData)
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Failed to update game data');
                }
                return response.json();
            })
            .then(data => {
                console.log(data.message); // Output success message
            })
            .catch(error => {
                console.error(error.message); // Output error message
            });
        }
    </script>
</body>
</html>
