# HeroQR - A Powerful PHP QR Code Library

[![Downloads](https://img.shields.io/packagist/dt/amirezaeb/heroqr?color=green)](https://packagist.org/packages/amirezaeb/heroqr)
[![License](https://img.shields.io/packagist/l/amirezaeb/heroqr?color=orange)](LICENSE)
[![PHP Version](https://img.shields.io/packagist/php-v/amirezaeb/heroqr?color=violet)](https://www.php.net/)
[![Build Status](https://img.shields.io/github/actions/workflow/status/AmirezaEb/HeroQR/CI.yml?branch=develop&label=build&logo=github)](https://github.com/AmirezaEb/HeroQR/actions)
[![Latest Stable Version](https://img.shields.io/packagist/v/amirezaeb/heroqr)](https://packagist.org/packages/amirezaeb/heroqr)

HeroQR is a design-focused QR rendering engine for PHP developers who need fine-grained control over geometry, styling, and multi-format output — while remaining fully compliant with ISO/IEC 18004.

## Why HeroQR?

Unlike traditional QR libraries that focus only on encoding, HeroQR provides:

- Advanced geometric styling (Shapes, Markers, Cursors)
- Multi-format rendering engine (PNG, SVG, PDF, WebP, EPS)
- Matrix-level access for custom rendering
- Built-in validation and structured data support
- ISO/IEC 18004 compliance

> [!CAUTION]
> **Beta Version**: You are currently viewing the documentation for **v1.2.0-beta**. This version includes experimental features like SVG styling.
> For the latest stable version, please [Click Here](https://github.com/amirezaeb/heroqr).

## Table of Contents

- [HeroQR Library](#heroqr---a-powerful-php-qr-code-library)
    - [Features](#features)
    - [Getting Started](#getting-started)
        - [1. Installation](#1-installation)
        - [2. Basic Usage](#2-basic-usage)
        - [3. Advanced Customization](#3-advanced-customization)
        - [4. Customizing Shapes, Markers, and Cursors (PNG & SVG Only)](#4-customizing-shapes-markers-and-cursors)
        - [5. Advanced Output Options](#5-advanced-output-options)
    - [Project Structure](#project-structure)
    - [Support & Sponsorship](#support--sponsorship)
    - [Contributing](#contributing)
    - [License](#license)
    - [Contact](#contact)

## Features

- **Advanced Customization Options:**   
    - **Logo Integration:** Adjust logo size and seamlessly embed images into QR codes.   
    - **Smart Layout:** Automatically adjusts margins and scaling for optimal readability.   
    - **Color Management:** Full control over foreground and background colors (RGB/RGBA), including transparency.
    - **Custom Labels:** Add and style text labels with control over color, size, alignment, and margins.

- **Unique Design Control (Geometric Styling):**  
  HeroQR allows you to move beyond standard squares by customizing the geometric shape of modules, markers, and cursors:   
    - `S` for Shape type.
    - `M` for Marker type.
    - `C` for Cursor type.
    - Available marker and cursor types:
      - **Shapes (S):** 4 unique module styles (`S1` - `S4`).
      - **Markers (M):** 6 professional corner patterns (`M1` - `M6`).
      - **Cursors (C):** 6 specialized cursor designs (`C1` - `C6`).
      - **Format Support:** These advanced geometric styles are currently fully supported in **PNG** and **SVG** formats.
      - **Future-Ready:** Support for additional formats (PDF, WebP, etc.) is currently in development and will be released in upcoming updates.

- **Multi-Format Data Encoding:**  
  - Generate QR codes for various use cases: URLs, plain text, emails, vCards (Business Cards), payment information, and more. Supports BASE64, UTF-8, and UTF-16 encoding.

- **Built-In Data Validation:**  
  - Automatic validation for URLs, emails, phone numbers, IP addresses, and Wi-Fi credentials to ensure generated codes are always functional.

- **Flexible Export Formats:**
  - Export your results in **SVG, PNG, PDF, Binary, GIF, EPS, and WebP**.

- **Reliability & Integration:**
    - **Framework Ready:** Optimized for modern frameworks like **Laravel**.
    - **Test-Driven:** Backed by 200+ automated tests and 1000+ assertions to ensure compliance with the ISO/IEC 18004 QR Code specification and maintain high reliability.

## Getting Started

### 1. Installation

Use [Composer](https://getcomposer.org/) to install the library. Also make sure you have enabled and configured the [GD extension](https://www.php.net/manual/en/book.image.php) if you want to generate images.

```bash
composer require amirezaeb/heroqr:1.2.0-beta
```

### 2. Basic Usage

- **Generate a simple QR code in just a few lines of code:**

#### Example:

```php
use HeroQR\Core\QRCodeGenerator;

# Initialize the QRCode generator
$qrCodeManager = new QRCodeGenerator();

# Build and generate the QR code
$qrCode = $qrCodeManager
    # Set the data to be encoded in the QR code
    ->setData('https://test.org') 
    # Generate the QR code in PNG format (default)
    ->generate();

# Save the image to your local directory
$qrCode->saveTo('qrcode'); 
```

Note: The saveTo() method automatically appends the correct file extension based on the selected format.

### 3. Advanced Customization

Take full control over the appearance and functionality of your QR codes. HeroQR includes **automatic data validation** to ensure your codes always work.

**Granular Customization:** Fine-tune colors, size, logos, and typography.  
**Smart Validation:** Use the optional `DataType` to automatically validate inputs like URL, Email, Phone, Wi-Fi, and more.

#### Example:

```php
use HeroQR\Core\QRCodeGenerator;
use HeroQR\DataTypes\DataType;

# Initialize the QRCode generator
$qrCodeManager = new QRCodeGenerator();

$qrCode = $qrCodeManager
    # data and validate it as an Email
    ->setData('aabrahimi1718@gmail.com', DataType::Email)  # Default DataType::None
    # Background color (RGBA: Red, Green, Blue, Alpha)
    ->setBackgroundColor(255, 255, 255, 0) # (RGBA) - Default: 255, 255, 255, 1
    # Foreground color (RGB: Red, Green, Blue)
    ->setColor(0, 100, 200) # (RGBA) - Default: 0, 0, 0, 1
    # size of the QR code
    ->setSize(1000) # Default: 800
    # logo to be embedded at the center
    ->setLogo('../assets/HeroExpert.png', 100) # Default: 80
    # margin around the QR code
    ->setMargin(0) # Default: 10
    # character encoding for the QR code
    ->setEncoding('CP866') # Default: UTF-8
    # error correction level for the QR code
    ->setErrorCorrectionLevel('Medium') # Default: High
    # block size mode to "None"
    ->setBlockSizeMode('None') # Default: Margin
    # Customize the label with text, alignment, color, and font size
    ->setLabel(
        # Label Text
        label: 'To Contact Me, Just Scan This QRCode',
        # Label alignment
        textAlign: 'left', # Default: center
        # Label text color 
        textColor: ['r' => 0, 'g' => 100, 'b' => 200], # Default: 0, 0, 0 
        # Label size
        fontSize: 35, # Default: 50
        # Label margin (Top, Right, Bottom, Left)
        margin: [15, 15, 15, 15] # Default: [0, 10, 10, 10]
    )
    # Generate the QR code in WebP format
    ->generate('webp');

# Save the generated QR code to a file
$qrCode->saveTo('custom-qrcode');
``` 

With these options, you can create visually appealing QR codes that align with your design needs.

### 4. Customizing Shapes, Markers, and Cursors

HeroQR allows you to fully customize the modules, markers, and cursors of your QR codes. Move beyond standard squares to create stylish, branded designs.

> [!IMPORTANT]
> This feature is available for **PNG** and **SVG** output formats.

#### Available Options:
- **Shapes (Modules):** `S1` to `S4`
- **Markers (Corners):** `M1` to `M6`
- **Cursors (Inner Markers):** `C1` to `C6`

#### Example:

```php
use HeroQR\Core\QRCodeGenerator;

# Initialize the QRCode generator
$qrCodeManager = new QRCodeGenerator();

$qrCode = $qrCodeManager
    ->setData('https://github.com/amirezaeb/heroqr')
    ->setSize(800)
    ->setBackgroundColor(0, 0, 0, 0) 
    ->setColor(0, 100, 200)
    # Customize the Style: S2 (Circle Shape), M2 (Circle Marker), C2 (Circle Cursor)
    ->generate('svg',[
            'Shape' => 'S2',
            'Marker' => 'M2',
            'Cursor' => 'C2'
        ]);

# Save the generated QR code with customizations
$qrCode->saveTo('custom-qr');
```

**Example QR Code outputs with different combinations:** Below are some examples of QR codes generated using various combinations of shapes, markers, and cursors:

|  Combination   | Shape (Body)     | Marker (Corner)    | Cursor (Inner)     |                                                Preview                                                 |
|:--------------:|------------------|--------------------|--------------------|:------------------------------------------------------------------------------------------------------:|
|  **S1-M1-C1**  | Square (Default) | Square (Default)   | Square (Default)   |  [View](https://raw.githubusercontent.com/AmirezaEb/AmirezaEb/main/assets/img/QrCode/Qr-S1-M1-C1.png)  |
|  **S2-M2-C2**  | Circle (Custom)  | Circle (Custom)    | Circle (Custom)    |  [View](https://raw.githubusercontent.com/AmirezaEb/AmirezaEb/main/assets/img/QrCode/Qr-S2-M2-C2.png)  |
|  **S3-M3-C3**  | Star (Custom)    | D-Drop-O (Custom)  | D-Drop-O (Custom)  |  [View](https://raw.githubusercontent.com/AmirezaEb/AmirezaEb/main/assets/img/QrCode/Qr-S3-M3-C3.png)  |
|  **S4-M4-C4**  | Diamond (Custom) | D-Drop-I (Custom)  | D-Drop-I (Custom)  |  [View](https://raw.githubusercontent.com/AmirezaEb/AmirezaEb/main/assets/img/QrCode/Qr-S4-M4-C4.png)  |
|  **S4-M5-C5**  | Diamond (Custom) | D-Drop-IO (Custom) | D-Drop-IO (Custom) |  [View](https://raw.githubusercontent.com/AmirezaEb/AmirezaEb/main/assets/img/QrCode/Qr-S4-M5-C5.png)  |
|  **S4-M6-C6**  | Diamond (Custom) | Square-O (Custom)  | Square-O (Custom)  |  [View](https://raw.githubusercontent.com/AmirezaEb/AmirezaEb/main/assets/img/QrCode/Qr-S4-M6-C6.png)  |

- **Experiment with Different Combinations:** In this section, you can experiment with various combinations to create unique QR codes that best suit your needs. Each combination will impact the appearance, from the corners to the positioning pointers.

### 5. Advanced Output Options

HeroQR provides advanced output capabilities, offering flexibility and compatibility for various use cases, from web embedding to raw data manipulation:

- **String Representation:** Retrieve the QR code as a raw binary string for direct processing.

- **Matrix Output:** Access the QR code as a matrix (2D array) of bits. This is perfect for custom rendering or logical checks.
    * Get it as a `Matrix` object.
    * Get it as a plain 2D array.
  
- **Base64 Encoding (Data URI):** Generate a Base64-encoded string, ideal for embedding directly into HTML `<img>` tags or CSS.

- **Format Versatility:** Save your QR codes in multiple formats including **PNG, SVG, GIF, WebP, EPS, and PDF**

#### Example:

```php
use HeroQR\Core\QRCodeGenerator;

# Initialize the QRCode generator
$qrCodeManager = new QRCodeGenerator();

$qrCode = $qrCodeManager
    # Set the data to be encoded in the QR code
    ->setData('https://test.org') 
    # Generate the QR code in PNG format (default)
    ->generate();

# 1. Get as a raw string
$string = $qrCode->getString();

# 2. Get as a Matrix object
$matrix = $qrCode->getMatrix();

# 3. Get as a 2D array (e.g., for custom CLI rendering)
$matrixArray = $qrCode->getMatrixAsArray();

# Get as Base64 Data URI (Ready for <img src="...">)
$dataUri = $qrCode->getDataUri();

# 5. Save in various formats
$qrCode->saveTo('qr_code_output');
```

## Project Structure

HeroQR follows a modular architecture designed for scalability, maintainability, and ease of use. Below is an overview of the core directory structure:

```text
src/
├── Contracts/   # Core interfaces and system abstractions
├── Core/        # Primary logic for QR code generation
├── DataTypes/   # Data definitions and automatic validation (WiFi, URL, etc.)
├── Managers/    # Customization and processing controllers
├── Customs/     # Advanced visual elements (Shapes, Cursors, Markers)
└── Tests/       # Unit and integration test suites
```
### Module Overview:

* **Contracts:** Defines interfaces for core components to ensure consistency and extensibility across the library.
* **Core:** The backbone of the system, handling the fundamental logic for QR code generation.
* **DataTypes:** Handles automatic validation for various inputs like WiFi, URL, and Email—saving you from manual checks.
* **Managers:** Orchestrates the customization process and manages the overall QR code life-cycle.
* **Customs:** The creative hub for advanced styling, including custom Shapes, Markers, and Cursors.
* **Tests:** Comprehensive unit and integration test suites ensuring the stability and correctness of every feature.

## Support & Sponsorship

If **HeroQR** has been helpful to your project, consider supporting its development. Your support helps keep the project maintained and inspires new features!

### 🌟 Ways to Support:
* **Star & Share:** Give the project a ⭐ and share it with your fellow developers.
* **Contribute:** Open issues or submit Pull Requests to help improve the library.
* **Donate (Crypto):** Due to limited access to international payment gateways, you can support me directly via **TON (The Open Network)**. Click the address below to copy:

- **Wallet Address:**  
  - **TON (The Open Network):**
      ```text
      UQCMRW381XBI7x5-BZIMPBS7oHOuHUMoJ8RLdpSZ3HjqBQjg
      ```
  - **USDT (TRC-20 / Tron Network)**
    ```text
    TEbQ2K3kWF1TjE4yRuqp6hTH8FRr7n7xGX 
    ```

### 🏢 Become a Sponsor:
If your company is interested in becoming an official or long-term sponsor and having your brand featured in the HeroQR documentation, we would love to hear from you!

Please refer to the [Contact](#contact) section to get in touch and discuss sponsorship opportunities.

## Contributing

We welcome contributions and appreciate your interest in improving HeroQR! Here is how you can help:

1. **Fork the repository:** Create your own copy of the repository by clicking the "Fork" button.

2. **Clone your fork:** Clone your forked repository to your local machine:
   ```bash
    git clone https://github.com/your-username/HeroQR.git
    ```
   
3. **Create a branch:** Create a new branch for your feature or bug fix:
    ```bash
    git checkout -b feature/amazing-feature
    ```
   
4. **Make changes & Test:** Implement your changes and ensure everything works as expected.

5. **Write tests:** Ensure your changes are covered by tests. If you're fixing a bug, add a test to verify the fix.

6. **Commit your changes:** Use clear, descriptive messages following the [Conventional](https://www.conventionalcommits.org/) Commits format:
    ```bash
    git commit -m "feat: add support for custom frame colors"
    ```

7. **Push your branch:** Push your changes to your fork
    ```bash
    git push origin feature/amazing-feature
    ```

8. **Open a Pull Request:** Submit your PR to the `main` branch. Provide a clear description of your changes and reference any relevant issues (e.g., `Fixes #123`).

We will review and merge your changes as soon as possible. Thank you for making HeroQR better! ❤️

## License

HeroQR is open-sourced software licensed under the [MIT License](LICENSE).

## Contact

For inquiries, feedback, or collaborations, feel free to reach out via any of the following channels:

* **Author:** Amirreza Ebrahimi
* **Email:** [aabrahimi1718@gmail.com](mailto:aabrahimi1718@gmail.com)
* **GitHub:** [Report an Issue](https://github.com/AmirezaEb/HeroQR/issues)
* **LinkedIn:** [Amirreza Ebrahimi](https://www.linkedin.com/in/amirezaeb)
* **Telegram:** [@a_m_b_r](https://t.me/a_m_b_r)

---

**Transform your projects with HeroQR today! 🚀**
