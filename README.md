✨ GACRKL Lost & Found Hub ✨ <br>
📝 Project Description
The GACRKL Lost & Found Hub is a dynamic web application designed to help the GACRKL college community connect and reunite lost belongings with their rightful owners. It provides a centralized, user-friendly platform for students and staff to report lost or found items, browse existing listings, engage in real-time community chat, and even have private, secure conversations with item reporters or the administrator. An integrated admin panel allows for efficient moderation of items and users, ensuring a safe and organized environment.

✨ Key Features
🔐 User Authentication: A robust sign-up and sign-in system with secure password hashing to protect user accounts.

🔍 Lost & Found Reporting:

Users can easily report lost or found items, providing detailed descriptions and crucial contact information.

📸 Option to upload a photo of the item, significantly increasing the chances of recovery.

Items are automatically removed from public view after 30 days of posting (unless marked as resolved) to keep listings fresh and relevant.

📚 Item Browsing:

View clearly separated lists for Lost Items and Found Items, making searching intuitive.

✅ "Mark as Found/Returned" Feature:

Original reporters have the exclusive ability to mark their own items as resolved, gracefully removing them from active listings once reunited.

💬 Community Chat:

A vibrant, public chat space where all logged-in users can communicate freely, share information, and offer assistance.

🔒 Private Chat:

Direct messaging capabilities! Users can initiate private conversations directly with the administrator for sensitive inquiries.

Admins can easily initiate private conversations with any registered user.

🔗 "Chat with Reporter" button on item listings allows direct, private contact with the item's reporter, facilitating quick reunions.

👑 Admin Panel:

A dedicated, powerful dashboard for administrators (admin user) to maintain order and security.

Comprehensive item management: View all reported items, including their images and current "reported" status.

Status Toggling: Admins can easily toggle an item's "Reported" status (e.g., to mark it as reviewed or actioned).

Deletion Control: Ability to delete any item or user from the system.

User Management: Overview and deletion capabilities for all registered users.

📱 Responsive Design: The entire application is meticulously optimized for seamless viewing and interaction across all devices – from mobile phones and tablets to desktop computers.

💖 User-Friendly Interface: A clean, intuitive, and aesthetically pleasing design, powered by Tailwind CSS, ensures a delightful user experience.

🚀 Technologies Used
🌐 Frontend:

HTML5: For robust page structure.

CSS3: Enhanced with Tailwind CSS CDN for rapid and responsive styling.

JavaScript (Vanilla JS): For dynamic DOM manipulation and efficient AJAX/Fetch API communication with the backend.

⚙️ Backend:

PHP: The core server-side scripting language handling all logic, file operations, and user session management.

🗄️ Data Storage:

JSON files: (users.json, items.json, chat.json, private_chats.json) used for persistent data storage.

uploads/ directory: Dedicated folder for securely storing all uploaded item images.

🛠️ Setup Instructions
Follow these detailed steps to get the GACRKL Lost & Found Hub up and running on your local machine or web server.

Prerequisites
A web server with PHP installed and configured (e.g., XAMPP, WAMP, Apache with PHP-FPM, Nginx with PHP-FPM).

A basic understanding of file permissions on your operating system/server is highly recommended.

Installation Steps
Clone the Repository:

git clone https://github.com/Dexter-code7/lostandfound.git
cd lostandfound

Place Files on Your Web Server:

Copy the entire lostandfound folder into your web server's document root.

XAMPP: C:\xampp\htdocs\

WAMP: C:\wamp64\www\

Linux (Apache): /var/www/html/ (or a subdirectory within)

Create Empty JSON Files:

Navigate into your project folder.

Create the following empty JSON files. They must contain an empty JSON array [] initially.

chat.json

items.json

users.json

private_chats.json

Using PowerShell (Windows):

Set-Content -Path chat.json -Value "[]"
Set-Content -Path items.json -Value "[]"
Set-Content -Path users.json -Value "[]"
Set-Content -Path private_chats.json -Value "[]"

Using Bash/Git Bash (Linux/macOS/Windows):

echo "[]" > chat.json
echo "[]" > items.json
echo "[]" > users.json
echo "[]" > private_chats.json

Create uploads/ Directory:

Inside your project folder, create a new directory named uploads. This is where item photos will be stored.

Set File and Folder Permissions (CRUCIAL!):

Incorrect permissions are the most common cause of errors. You need to ensure your web server process has read and write access to the necessary files and folders.

Using Webmin / FTP Client (e.g., FileZilla) / SSH (Linux/macOS):

For PHP files (.php): Set permissions to 0755 (rwxr-xr-x).

register.php, login.php, logout.php, check_session.php, send_chat_message.php, get_chat_messages.php, submit_item_to_json.php, get_items_from_json.php, get_all_items.php, delete_item.php, get_all_users.php, delete_user.php, report_item.php, toggle_item_report_status.php, mark_item_resolved.php, get_private_chat_users.php, get_private_messages.php, send_private_message.php.

For JSON data files (.json): Set permissions to 0666 (rw-rw-rw-). This is often necessary on shared/free hosting for PHP to write to them.

chat.json, items.json, users.json, private_chats.json

For uploads/ directory: Set permissions to 0777 (rwxrwxrwx). This is often required for file uploads on shared/free hosting.

For your main project folder (gacrkl-lost-found-app): Set permissions to 0755.

Start Your Web Server:

If using XAMPP/WAMP, start the Apache module from the control panel.

If using a custom server setup, ensure Apache/Nginx and PHP-FPM services are running.

Access the Application:

Open your web browser and navigate to the project's URL:

Local: http://localhost/lostandfound (or http://localhost:8080/lostandfound/ if Apache port is 8080)

Live Server: http://yourdomain.com/lostandfound/ (or just http://yourdomain.com/ if placed in the root).

🔑 Admin Access
To access the powerful admin panel:

Register a new user with the exact username admin on the sign-up form.

Log in with the admin username and the password you set.

The "Admin Panel" link will then become visible in the main navigation.

⚠️ Security Disclaimer (IMPORTANT!)
This project is built using PHP and JSON files for data storage and basic session management. This setup is intended solely for demonstration, learning, and personal prototyping purposes.

It is NOT suitable for production environments or storing sensitive data due to significant security vulnerabilities, including but not limited to:

Plain JSON File Storage: Data is stored in easily accessible plain text JSON files, which are prone to direct access if server permissions are misconfigured.

No Database: Lacks the transactional integrity, performance, and robust security features of a proper database (e.g., MySQL, PostgreSQL).

Basic Authentication: The authentication system is simplified and relies on basic session management without advanced security measures (e.g., CSRF protection, secure cookie flags, rate limiting for login attempts).

Concurrency Issues: Simultaneous writes to JSON files (especially for chat) can lead to data corruption or loss.

Limited Input Validation/Sanitization: While basic sanitization is present, a production application requires comprehensive input validation to prevent XSS, SQL injection (if using a database), and other attacks.

No Role-Based Access Control (RBAC): Admin access is based on a hardcoded username check, which is highly insecure.

For a production application, it is strongly recommended to migrate to a robust database (like MySQL), implement a secure PHP framework (e.g., Laravel, Symfony) with built-in authentication, and use proper security practices.

💡 Future Improvements
Database Integration: Migrate from JSON files to a relational database (e.g., MySQL) for robust data storage, querying, and security.

Real-time Chat: Implement WebSockets for true real-time chat functionality instead of polling.

Search & Filters: Add advanced search and filtering options for lost and found items.

User Profiles: Allow users to manage their profiles and view their own reported items.

Notifications: Implement notifications for new messages or matched items.

Improved UI/UX: Further refine the user interface and user experience.

Advanced Admin Features: More granular control over items, users, and content.

Image Optimization: Implement server-side image resizing and optimization for better performance.

📄 License
This project is open-sourced under the MIT License. See the LICENSE file for more details.
