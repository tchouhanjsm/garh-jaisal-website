#!/bin/sh
# Double-click on Mac/Linux: starts the Content Studio and opens it in your browser.
cd "$(dirname "$0")" && exec node tools/content-studio/start.js
