# Sefaria Dashboard for Nextcloud

A small Nextcloud app that adds today's Sefaria learning schedules to the dashboard.

# **AI DISCLOSURE**

**This project was built with AI assistance. The tool used was GitHub Copilot in Visual Studio Code. The exact underlying model name was not exposed in the development session, so no specific model name is claimed here. AI assistance was used for project setup, PHP and Nextcloud code, debugging, documentation, and validation.**

## What it does

- Registers a server-rendered `IAPIWidgetV2` dashboard widget.
- Reads the public Sefaria Calendars API at `https://www.sefaria.org/api/calendars`.
- Displays up to seven schedules as links to Sefaria.
- Refreshes the widget hourly and shows a graceful empty state if Sefaria is unavailable.

## Install for development

1. Copy this directory to the Nextcloud `apps/` directory.
2. Run `composer install --no-dev` in the app directory if dependencies are added later.
3. Enable it with `occ app:enable sefaria_dashboard`.
4. Open the Nextcloud dashboard.

The app targets Nextcloud 31 and newer, and PHP 8.1 or newer.

## References

- [Nextcloud dashboard widgets](https://docs.nextcloud.com/server/latest/developer_manual/digging_deeper/dashboard.html#dashboard)
- [Sefaria Calendars API](https://developers.sefaria.org/reference/get-calendars)