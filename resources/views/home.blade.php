<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bestdecoders - Shopify Apps & WordPress Plugins</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 2rem;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .logo {
            font-size: 3rem;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 1rem;
        }
        
        .tagline {
            font-size: 1.2rem;
            color: #666;
            margin-bottom: 2rem;
        }
        
        .services {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin: 3rem 0;
        }
        
        .service-card {
            background: #f8f9ff;
            padding: 2rem;
            border-radius: 15px;
            border: 2px solid #e5e7ff;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .service-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(102, 126, 234, 0.2);
        }
        
        .service-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        
        .service-title {
            font-size: 1.5rem;
            font-weight: bold;
            color: #333;
            margin-bottom: 1rem;
        }
        
        .service-description {
            color: #666;
            line-height: 1.6;
        }
        
        .cta-section {
            margin: 3rem 0;
            padding: 2rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 15px;
            color: white;
        }
        
        .cta-title {
            font-size: 2rem;
            margin-bottom: 1rem;
        }
        
        .cta-text {
            font-size: 1.1rem;
            margin-bottom: 2rem;
            opacity: 0.9;
        }
        
        .btn {
            display: inline-block;
            padding: 1rem 2rem;
            margin: 0.5rem;
            text-decoration: none;
            border-radius: 50px;
            font-weight: bold;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .btn-primary {
            background: white;
            color: #667eea;
        }
        
        .btn-primary:hover {
            background: #f0f2ff;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        
        .btn-secondary {
            background: transparent;
            color: white;
            border: 2px solid white;
        }
        
        .btn-secondary:hover {
            background: white;
            color: #667eea;
        }
        
        .contact-info {
            margin-top: 2rem;
            padding: 1.5rem;
            background: #f8f9ff;
            border-radius: 10px;
            border-left: 4px solid #667eea;
        }
        
        .contact-info h3 {
            color: #333;
            margin-bottom: 1rem;
        }
        
        .contact-info a {
            color: #667eea;
            text-decoration: none;
            font-weight: bold;
        }
        
        .contact-info a:hover {
            text-decoration: underline;
        }
        
        @media (max-width: 768px) {
            .container {
                margin: 1rem;
                padding: 1.5rem;
            }
            
            .logo {
                font-size: 2rem;
            }
            
            .services {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
            
            .service-card {
                padding: 1.5rem;
            }
            
            .cta-title {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">Bestdecoders</div>
        <p class="tagline">Building powerful solutions for your digital presence</p>
        
        <div class="services">
            <div class="service-card">
                <div class="service-icon">🛍️</div>
                <h3 class="service-title">Shopify Apps</h3>
                <p class="service-description">
                    Custom Shopify applications designed to enhance your store's functionality and boost sales. 
                    From inventory management to customer engagement tools.
                </p>
            </div>
            
            <div class="service-card">
                <div class="service-icon">⚡</div>
                <h3 class="service-title">WordPress Plugins</h3>
                <p class="service-description">
                    Professional WordPress plugins that extend your website's capabilities. 
                    SEO tools, performance optimizers, and custom functionality solutions.
                </p>
            </div>
        </div>
        
        <div class="cta-section">
            <h2 class="cta-title">Ready to Get Started?</h2>
            <p class="cta-text">
                We specialize in creating high-quality Shopify apps and WordPress plugins that solve real business problems.
                Let's discuss your project and bring your ideas to life.
            </p>
            
            <a href="https://bestdecoders.com/" class="btn btn-primary" target="_blank">
                Visit Our Website
            </a>
            <a href="https://bestdecoders.com/contact-us/" class="btn btn-secondary" target="_blank">
                Contact Us
            </a>
        </div>
        
        <div class="contact-info">
            <h3>📧 Get in Touch</h3>
            <p>Have questions or Want to discuss a project together? We're here to help!</p>
            <p>Email us at: <a href="mailto:support@bestdecoders.com">contact@bestdecoders.com</a></p>
        </div>
    </div>
</body>
</html>