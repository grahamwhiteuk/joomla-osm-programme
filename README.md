# Joomla OSM Programme Module

A Joomla module that integrates with [Online Scout Manager (OSM)](https://www.onlinescoutmanager.co.uk/) to display upcoming scout section meetings on your Joomla website.

## 📋 Description

This module allows scout groups to automatically display their next scheduled meeting from OSM on their Joomla website. It shows the meeting title, date, time, and optional notes for parents, keeping your website visitors informed about upcoming activities.

## ✨ Features

- **Automatic Updates**: Fetches the next scheduled meeting from OSM
- **OAuth Authentication**: Secure integration using OSM's OAuth API
- **Smart Caching**: Reduces API calls by caching responses in the database
- **Customizable Display**:
  - Show/hide meeting notes
  - Show/hide module when no meeting is scheduled
  - Custom CSS styling support
- **Multi-Term Support**: Automatically searches the next term if no meetings are found in the current term
- **Responsive Design**: Works seamlessly with your Joomla template

## 🔧 Requirements

- **Joomla**: 4.x and above (may work with Joomla 3.x - feedback welcome)
- **PHP**: 7.4 or higher
- **MySQL**: 5.6 or higher
- **OSM Account**: With section access and API credentials

## 📦 Installation

### Method 1: Via Joomla Extension Manager

1. Download the latest release from the [releases page](https://github.com/grahamwhiteuk/joomla-osm-programme/releases)
2. Log into your Joomla administrator panel
3. Navigate to **System → Install → Extensions**
4. Upload the `joomla-osm-programme.zip` file
5. Click **Upload & Install**

### Method 2: Build from Source

```bash
git clone https://github.com/grahamwhiteuk/joomla-osm-programme.git
cd joomla-osm-programme
./build.sh
```

Then install the generated `joomla-osm-programme.zip` file via the Joomla Extension Manager.

## ⚙️ Configuration

### 1. Get OSM API Credentials

1. Log into [Online Scout Manager](https://www.onlinescoutmanager.co.uk/)
2. Go to **Settings → My Account Details → Developer Tools**
3. Click **Create Application**
4. Name your application (e.g., "My Scout Group Website")
5. Click **Save**
6. Copy the **OAuth Client ID** and **OAuth Client Secret** to your password manager or other secure location

### 2. Configure the Module

1. In Joomla admin, go to **Content → Site Modules**
2. Find and click on **OSM - Programme**
3. Configure the following settings:

#### Required Settings

- **OSM OAuth Client ID**: Paste the Client ID from OSM
- **OSM OAuth Client Secret**: Paste the Client Secret from OSM
- **Section Name or ID**: Enter your section name (e.g., "Beavers - Kits Colony") or numeric section ID

#### Optional Settings

- **Show When there is no Next Meeting Scheduled**: Choose whether to display a message when no meeting is found (default: Show)
- **Show Meeting Notes**: Choose whether to display the meeting notes for parents (default: Show)
- **Custom CSS**: Add custom CSS to style the module output

### 3. Assign to Module Position

1. Select the **Position** where you want the module to appear
2. Set **Status** to **Published**
3. Configure **Menu Assignment** to control where the module appears
4. Click **Save & Close**

> [!TIP]
> To configure multiple sections, tick the checkbox in the module list and use the Actions dropdown button to duplicate the module and configure each section separately.

## 🎨 Styling

The module uses the following CSS classes for styling:

- `.mod_osm` - Container div
- `.mod_osm_header` - Meeting title
- `.mod_osm_datetime` - Date and time container
- `.mod_osm_date` - Date span
- `.mod_osm_time` - Time span
- `.mod_osm_notes` - Meeting notes container

### Default Styling

```css
.mod_osm_header {
    font-weight: 500;
    font-size: 1.50rem;
}
.mod_osm_datetime {
    margin-bottom: .5rem;
    font-style: italic;
}
.mod_osm_notes pre {
    margin: 0;
    white-space: pre-wrap;
}
```

You can override these styles in the module configuration's **Custom CSS** field or other custom CSS fields on your Joomla pages.

## 🗄️ Database

The module creates a table `#__mod_osm` to cache API responses, reducing the number of calls to OSM's API and improving performance.

## 🔒 Security

- Uses OAuth 2.0 for secure authentication
- API credentials are stored securely in Joomla's configuration
- Access tokens are cached and automatically refreshed
- No user passwords are stored or transmitted

## 🐛 Troubleshooting

### "Error: unable to get token"
- Verify your OAuth Client ID and Client Secret are correct
- Ensure your OSM application is active
- Check that your server can make outbound HTTPS connections

### "Error: unable to find the section"
- Verify the section name matches exactly (including hyphens)
- Try using the numeric section ID instead
- Ensure you have access to the section in OSM

### "Error: unable to find the current term"
- Ensure your section has terms configured in OSM
- Check that the term dates are set correctly
- Verify the current date falls within a term period

### Module not displaying
- Check that the module is **Published**
- Verify the module position exists in your template
- Check the **Menu Assignment** settings
- Review the **Access Level** settings

## 📝 License

This project is licensed under the GNU General Public License v2.0 or later. See the [LICENSE](LICENSE) file for details.

## 🤝 Contributing

Contributions, issues, and feature requests are welcome! Feel free to check the [issues page](https://github.com/grahamwhiteuk/joomla-osm-programme/issues).

## 📮 Support

If you encounter any issues or have questions:

1. Check the [Troubleshooting](#-troubleshooting) section
2. Search existing [issues](https://github.com/grahamwhiteuk/joomla-osm-programme/issues)
3. Create a new issue with detailed information about your problem
