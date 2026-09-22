# Sefaria Dashboard for Nextcloud

A small Nextcloud app that adds today's Sefaria learning schedules to the dashboard.

# **AI DISCLOSURE**

**This project was built with AI assistance. The tool used was GitHub Copilot in Visual Studio Code.  AI assistance was used for project setup, PHP and Nextcloud code, debugging, documentation, and validation.**

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

## Releases

GitHub Actions validates pushes and pull requests. Every commit pushed to `main` creates a uniquely tagged GitHub prerelease archive and publishes it as a nightly build to the Nextcloud App Store. The workflow requires the `APP_PRIVATE_KEY` and `APPSTORE_TOKEN` repository secrets for App Store publishing.

To create a stable release, update the version in `appinfo/info.xml`, update `CHANGELOG.md`, commit those changes, and push a matching tag:

```bash
git tag v0.1.0
git push origin v0.1.0
```

The workflow checks the tag and app version, then publishes `sefaria_dashboard-0.1.0.tar.gz` to the GitHub Release and Nextcloud App Store. The archive is structured for Nextcloud installation and excludes development files such as `vendor/`, `.git/`, and `.vscode/`.

## References

- [Nextcloud dashboard widgets](https://docs.nextcloud.com/server/latest/developer_manual/digging_deeper/dashboard.html#dashboard)
- [Sefaria Calendars API](https://developers.sefaria.org/reference/get-calendars)
- [R0Wi/nextcloud-appstore-push-action](https://github.com/R0Wi/nextcloud-appstore-push-action)