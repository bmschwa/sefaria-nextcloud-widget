# Sefaria Dashboard for Nextcloud

A small Nextcloud app that displays Sefaria learning schedules on the dashboard.

[![Powered by Sefaria](https://files.readme.io/87c5652-image.png)](https://help.sefaria.org/hc/en-us/articles/17388247452956-What-is-Sefaria)

This project uses data from [Sefaria](https://www.sefaria.org/) and is an independent third-party project, not developed or endorsed by Sefaria. See Sefaria's [name and logo usage guidance](https://developers.sefaria.org/docs/usage-of-our-name-and-logo).

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

The app targets Nextcloud 31 and up with PHP 8.1 or newer.

### CLI for Local Builds

_Windows_
```
$ErrorActionPreference='Stop'; $v=([xml](Get-Content -Raw appinfo/info.xml)).info.version; $a="sefaria_dashboard-$v.tar.gz"; $r=Join-Path $env:TEMP "sefaria-package-$([guid]::NewGuid())"; $s=Join-Path $r sefaria_dashboard; New-Item -ItemType Directory -Path $s -Force | Out-Null; Get-ChildItem -Force | Where-Object Name -notin '.git','.github','.vscode','vendor','sefaria_dashboard.key',$a | Copy-Item -Destination $s -Recurse -Force; curl.exe --fail --location --retry 3 --output (Join-Path $s 'appinfo/icon.png') 'https://files.readme.io/87c5652-image.png'; tar.exe -czf $a -C $r sefaria_dashboard; Remove-Item $r -Recurse -Force; Write-Host "Created $a"
```

## Releases

GitHub Actions validates every push and pull request. Every commit pushed to `main` creates a uniquely tagged GitHub prerelease archive and publishes it as a nightly build to the Nextcloud App Store. The workflow requires the `APP_PRIVATE_KEY` and `APPSTORE_TOKEN` repository secrets for App Store publishing. The archive is structured for Nextcloud installation and excludes development files such as `vendor/`, `.git/`, and `.vscode/`.

## References

- [Nextcloud dashboard widgets](https://docs.nextcloud.com/server/latest/developer_manual/digging_deeper/dashboard.html#dashboard)
- [Sefaria Calendars API](https://developers.sefaria.org/reference/get-calendars)
- [R0Wi/nextcloud-appstore-push-action](https://github.com/R0Wi/nextcloud-appstore-push-action)