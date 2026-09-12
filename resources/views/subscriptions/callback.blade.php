<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payment Result</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { font-family: system-ui, -apple-system, sans-serif; background: #f5f6fa; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; }
        .card { background: #fff; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); padding: 2.5rem; max-width: 420px; text-align: center; }
        .icon { font-size: 3rem; margin-bottom: 1rem; }
        .success { color: #16a34a; }
        .failed { color: #dc2626; }
        .pending { color: #d97706; }
        h1 { font-size: 1.25rem; margin: 0 0 .5rem; }
        p { color: #555; margin: 0 0 1.5rem; }
        a.button { display: inline-block; background: #2563eb; color: #fff; text-decoration: none; padding: .6rem 1.5rem; border-radius: 8px; }
    </style>
</head>
<body>
    <div class="card">
        @if ($error)
            <div class="icon failed">&#10007;</div>
            <h1>Payment Status Unavailable</h1>
            <p>{{ $error }}</p>
        @elseif ($transaction && $transaction->status === 'completed')
            <div class="icon success">&#10003;</div>
            <h1>Payment Successful</h1>
            <p>Your subscription has been activated. Thank you!</p>
        @elseif ($transaction && $transaction->status === 'failed')
            <div class="icon failed">&#10007;</div>
            <h1>Payment Failed</h1>
            <p>Your payment could not be completed. Please try again.</p>
        @else
            <div class="icon pending">&#8987;</div>
            <h1>Payment Pending</h1>
            <p>We are still confirming your payment status.</p>
        @endif

        <a class="button" href="{{ route('owner.subscription') }}">Back to Subscription</a>
    </div>
</body>
</html>
