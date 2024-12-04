<x-app-layout>

    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8d7da;
            color: #721c24;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;Qà
            align-items: center;
            height: 100vh;
        }
        .error-container {
            background-color: #f5c6cb;
            padding: 20px 40px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        h1 {
            font-size: 24px;
            margin-bottom: 10px;
        }
        p {
            font-size: 18px;
            margin: 0;
        }
    </style>
    <div class="error-container">
        <h1>Si è verificato un errore</h1>
        <p>{{ $message }}</p>
    </div>
</x-app-layout>
