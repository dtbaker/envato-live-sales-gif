# envato-live-sales-gif

More details on the forums: http://codecanyon.net/forums/thread/sales-notifications-for-item-pages/186647

A live sales notification GIF you can place on Envato item pages.

This probably won't work if you have nginx or some other sort of caching infront of apache.

PHP needs to stream data straight to the browser as soon as it's generated.

Tested and works on a stock standard Ubuntu Server AWS instance under Apache.

needs php write permissions to create cache_statement.json in the same folder.
