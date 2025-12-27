# 📧 Email Deliverability Intelligence API 🚀

A professional, robust **PHP API designed to analyze email addresses** beyond simple syntax checks. This tool evaluates the underlying domain infrastructure, authentication protocols, and reputation signals to predict whether an email will land in the inbox or the spam folder.

<p align="center">
  <i>(Ensure your emails reach their intended destination with advanced analysis.)</i>
</p>

[![PHP](https://img.shields.io/badge/PHP-8.0%2B-777BB4?style=flat-square&logo=php&logoColor=white)](https://www.php.net/)
[![License](https://img.shields.io/badge/license-MIT-green?style=flat-square)](LICENSE)
[![Maintenance](https://img.shields.io/badge/Maintained%3F-yes-green.svg?style=flat-square)](https://github.com/YOUR-USERNAME)

---

## About The Project 📍

Most email validators only check if an address exists. This API solves the real problem: **Deliverability**. It analyzes the technical setup of the sender's domain to calculate the risk of hitting the spam folder.

**Key Concepts:**
* **Beyond Validation:** Moving from "Is this email valid?" to "Will this email get delivered?"
* **Infrastructure Analysis:** Deep dives into DNS records that impact deliverability.
* **Scoring Engine:** Provides a calculative score (0-100) and risk level based on weighted factors.

---

##  Features 📍

### 🧠 Core Analysis Engine
* **🛡️ DNS Authentication Check:** Verifies the existence and strength of SPF, DKIM, and DMARC records.
* **📅 Domain Age & Reputation:** Analyzes domain longevity and checks against known reputation indicators.
* **🗑️ Disposable Email Detection:** Identifies temporary or "burner" email providers.
* **🏢 Provider Detection:** Identifies major providers (Google Workspace, Outlook, Zoho) to adjust deliverability expectations.

### ⚙️ API & Architecture
* **📈 Intelligent Scoring:** Custom weighted scoring system defined in configuration.
* **🚀 Caching Layer:** Implements caching to speed up repeated lookups and reduce external DNS queries.
* **🛡️ Rate Limiting:** Built-in protection against abuse.
* **📝 Comprehensive Logging:** Tracks access and errors for debugging and monitoring.

---

## 🛠 Tech Stack

* **Core:** PHP 8.0+ (Strict typing).
* **Architecture:** Custom MVC-lite structure with separate Services (Validators) and Utilities.
* **Data/Storage:** Flat-file storage for logs and local caching (no Database required initially).

---

## ⚙️ Installation Guide

You can deploy this API on any standard web server supporting PHP (Apache/Nginx).

### 📂 Prerequisites

1.  **PHP 8.0** or higher installed.
2.  **Composer** (Optional, if you decide to add external packages later).
3.  `php-xml` and `php-mbstring` extensions enabled.

### 🚀 Setup Steps

1.  **Clone the Repo:**
    ```bash
    git clone https://github.com/KING-OF-FLAME/email-deliverability-intelligence-api.git
    ```
2.  **Move Files:**
    Place the project folder onto your web server (e.g., `/var/www/html/api`).
3.  **Configure Web Server:**
    Point your web server's document root to the `public/` directory.

    * *Apache Example (.htaccess is included in public/):* Ensure `mod_rewrite` is enabled.
    * *Nginx Example:*
        ```nginx
        root /path/to/your/project/public;
        index index.php;
        location / {
            try_files $uri $uri/ /index.php?$query_string;
        }
        ```
4.  **Permissions:**
    Ensure the webserver user (e.g., `www-data`) has write permissions for the `storage/` and `logs/` directories.
    ```bash
    chmod -R 755 storage logs
    chown -R www-data:www-data storage logs
    ```

---

## 🔧 Configuration & Settings

Navigate to the `config/` directory to customize the API behavior.

### 1. Main Configuration (`config/config.php`)
Set up your environment settings, API keys (if adding external services later), and base paths.

### 2. Adjusting Scoring (`config/weights.php`)
Control how heavily different factors impact the final deliverability score.

```
// Example snippet from weights.php
return [
    'spf_present' => 20,
    'dmarc_enforced' => 30,
    'domain_age_years' => 5,
    // ...
];
```
### 3. Managing Providers & Disposable Domains

```
config/providers.php: Define known email service provider MX signatures.
config/disposable.php: Update the list of known throwaway email domains.
```

📂 Folder Structure

```
email-deliverability-api/
├── config/             # Configuration files
│   ├── config.php      # Main app config
│   ├── providers.php   # Email provider definitions
│   ├── disposable.php  # List of disposable domains
│   └── weights.php     # Scoring logic weights
├── public/             # Web server document root
│   ├── index.php       # Entry point
│   └── .htaccess       # Apache routing
├── src/                # Application Core Code
│   ├── Validator.php   # Main validation orchestrator
│   ├── DNSChecker.php  # SPF/DKIM/DMARC analyzer
│   ├── ProviderDetector.php # MX record analyzer
│   ├── Reputation.php  # Reputation logic
│   ├── DomainAge.php   # Domain creation checks
│   ├── Scorer.php      # Final score calculation
│   ├── Cache.php       # Caching mechanism
│   ├── RateLimiter.php # API request limiting
│   └── Response.php    # Standardized JSON output
├── logs/               # Application logs
│   ├── access.log
│   └── error.log
├── storage/            # Local file storage
│   └── cache/          # Cache files location
└── .gitignore          # Git ignore rules
```
---
### 🤝 Contributions
Contributions are what make the open-source community such an amazing place to learn, inspire, and create. Any contributions you make are greatly appreciated.

1) Fork the Project.
2) Create your Feature Branch (git checkout -b feature/AmazingFeature).
3) Commit your Changes (git commit -m 'Add some AmazingFeature').
4) Push to the Branch (git push origin feature/AmazingFeature).
5) Open a Pull Request.
---
## 📧 Contact

Github: [KING OF FLAME](https://github.com/KING-OF-FLAME)
Instagram: [yash.developer](https://instagram.com/yash.developer)
---
