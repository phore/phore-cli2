# Architecture

## CLI output ownership

CLI execution must never automatically enable, replace, flush, clean, or otherwise manage PHP output buffering. In particular, CLI framework code must not call `ob_start()`, `ob_get_clean()`, `ob_end_clean()`, or equivalent buffering mechanisms as part of normal command execution. Command output is written directly to the active output stream, while any deliberate output buffering remains the responsibility of the embedding application or caller.
