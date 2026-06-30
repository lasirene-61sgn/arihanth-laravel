<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Arihanth Jewellers - Mac Collection</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: Arial, sans-serif;
            background: #f7fafc;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        
        .card {
            background: #fff;
            border: 1px solid #ddd;
            padding: 24px;
            width: 100%;
            max-width: 400px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(0,0,0,0.15);
        }
        
        .company {
            color: #2d3748;
            font-weight: bold;
            font-size: 1.4em;
            text-align: center;
            padding-bottom: 12px;
            border-bottom: 2px solid #e2e8f0;
            margin-bottom: 16px;
        }
        
        .mac-badge {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.85em;
            font-weight: 600;
            margin-bottom: 12px;
        }
        
        .detail {
            margin: 10px 0;
            color: #4a5568;
            font-size: 0.95em;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .detail strong {
            min-width: 70px;
            color: #2d3748;
        }
        
        .mac-products {
            margin-top: 16px;
            padding-top: 16px;
            border-top: 1px dashed #e2e8f0;
        }
        
        .mac-products h4 {
            color: #2d3748;
            margin-bottom: 10px;
            font-size: 1em;
        }
        
        .product-list {
            list-style: none;
            font-size: 0.9em;
            color: #4a5568;
        }
        
        .product-list li {
            padding: 4px 0;
            padding-left: 18px;
            position: relative;
        }
        
        .product-list li::before {
            content: "•";
            color: #667eea;
            font-weight: bold;
            position: absolute;
            left: 0;
        }
        
        .cta-button {
            display: block;
            width: 100%;
            margin-top: 20px;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-align: center;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: opacity 0.2s;
        }
        
        .cta-button:hover {
            opacity: 0.95;
        }
        
        /* Responsive Breakpoints */
        @media (max-width: 480px) {
            .card {
                padding: 20px;
                border-radius: 10px;
            }
            
            .company {
                font-size: 1.2em;
            }
            
            .detail {
                font-size: 0.9em;
                flex-direction: column;
                align-items: flex-start;
                gap: 4px;
            }
            
            .detail strong {
                min-width: auto;
            }
        }
        
        @media (min-width: 768px) {
            .card {
                max-width: 450px;
                padding: 28px;
            }
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="company">ARIHANTH JEWELLERS PVT LTD</div>
        
        <div class="detail"><strong>📞 Phone:</strong> <a href="tel:+919169164949" style="color:#4a5568;text-decoration:none">+91 91691 64949</a></div>
        <div class="detail"><strong>✉️ Email:</strong> <a href="mailto:contactaajpl@gmail.com" style="color:#4a5568;text-decoration:none">contactaajpl@gmail.com</a></div>
        <div class="detail"><strong>📍 Location:</strong> Arihanth Jewellers Pvt Ltd
7th Floor, Prashanth Gold, 1/21, (39-40/21), North Usman Road, T.Nagar, Chennai - 600017</div>
        
       
        <a href="tel:+919169164949" class="cta-button">📞 Contact Us Today</a>
    </div>
</body>
</html>