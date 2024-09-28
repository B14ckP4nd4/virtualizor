# Virtualizor Package

![Version](https://img.shields.io/badge/version-0.1.9-blue)
![Laravel](https://img.shields.io/badge/Laravel-11.x-red?style=flat-square&logo=laravel)
![Packagist Version](https://img.shields.io/packagist/v/blackpanda/virtualizor?style=flat-square)
![Downloads](https://img.shields.io/packagist/dt/blackpanda/virtualizor?style=flat-square)
![License](https://img.shields.io/github/license/B14ckP4nd4/virtualizor?style=flat-square)
![GitHub Issues](https://img.shields.io/github/issues/B14ckP4nd4/virtualizor?style=flat-square)
![GitHub Stars](https://img.shields.io/github/stars/B14ckP4nd4/virtualizor?style=flat-square)
![GitHub Forks](https://img.shields.io/github/forks/B14ckP4nd4/virtualizor?style=flat-square)
![PHP Version](https://img.shields.io/packagist/php-v/blackpanda/virtualizor?style=flat-square)


A PHP package for interacting with Virtualizor's API, providing a simple and efficient way to manage your Virtual Private Servers (VPS), IP pools, storage, and more.

## Table of Contents

- [Installation](#installation)
- [Usage](#usage)
- [Features](#features)
- [Methods](#methods)
- [Contributing](#contributing)
- [License](#license)

## Installation

You can install the package via Composer. Run the following command in your terminal:

```bash
composer require blackpanda/virtualizor
```

## Usage

To get started with the Virtualizor package, include it in your PHP script and initialize it with your API credentials.

```php

require 'vendor/autoload.php';

use BlackPanda\Virtualizor\Virtualizor;

// Initialize the Virtualizor client
$virtualizor = new Virtualizor('your_ip', 'your_port', 'your_api_key', 'your_api_secret');

// Example: Fetch VPSes
$vpsList = $virtualizor->getVPSes();
print_r($vpsList);
```

## Features

- Fetch and manage Virtual Private Servers (VPS)
- Manage IP pools
- Manage storage resources
- Start, stop, and restart VPS instances
- Support for various server actions

## Methods

The following methods are available in the package:

    getVPSes(): Retrieve a list of all VPS instances.
    createVPS(): Create a new VPS instance.
    deleteVPS(): Delete a VPS instance.
    start(): Start a VPS instance.
    stop(): Stop a VPS instance.
    suspend(): Suspend a VPS instance.
    resume(): Resume a suspended VPS instance.
    getVPSesStatus(): Retrieve the status of all VPS instances.

For more details on each method, please refer to the code documentation.


## Contributing

Contributions are welcome! If you have suggestions for improvements or find a bug, please open an issue or submit a pull request.

### Steps to Contribute:

1. Fork the repository.
2. Create a new branch (git checkout -b feature/YourFeature).
3. Make your changes and commit them (git commit -m 'Add some feature').
4. Push to the branch (git push origin feature/YourFeature).
5. Open a pull request.

## License

This package is licensed under the MIT License. See the LICENSE file for more information.
Author

This package is maintained by [B14ckP4nd4](https://github.com/B14ckP4nd4).
