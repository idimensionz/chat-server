# To execute these targets, type:
#     make [target-name]
# Example:
#     make all-tests
# will run all the unit tests in this repository.
SHELL := /bin/bash

# Local targets - run these from your computer, not the VM
install-dependencies:
	composer install

# run all unit tests
all-tests:
	vendor/bin/phpunit

# run all unit tests and generate code coverage HTML.
# Open the code-coverage/index.html in your web browser to view the report.
code-coverage-report:
	vendor/bin/phpunit --dump-xdebug-filter build/xdebug-filter.php
	vendor/bin/phpunit --prepend build/xdebug-filter.php --coverage-html code-coverage tests
