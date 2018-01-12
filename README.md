# Simple-PHP-and-MS-SQL-DB-Query-Tool
Simple PHP and MS-SQL DB Query Tool

This "tool" is a barebones PHP webpage for providing an interface to submit a search query to an MS-SQL database, and then displays the results of said query in a table. 

Installation:
- The thing was built for an IIS + PHP 7.0 environment. It might work elsewhere but that's untested.
  My implementation saw the page hosted as a new IIS site, in the default IIS directory (C:/inetput/wwwroot/[some-dir]).
- You need to configure your database environment variables in the searchCode.php file. 
  They're all at the top of the file and hopefully easy to identify.
  
Usage:
- Just visit the web address you configured the page to use in IIS.

Some improvements I'd like to make: 
- Connection to the DB could probably be better. I'd like to provide for a way to connect to the DB in a password-less fashion. Though that probably requires configuring allowed connections on the DB side more than it does the app...
- Improve the handling of multiple search terms. Currently multiple terms are exploded into an array and each array element is used in an individual SQL query. This was done as an easier way to handle and report terms that returned no result. See the link to blog post 2 below for more.
- Make it look nicer maybe?

I have a blog where I've talked about this thing. Maybe you'll find those posts useful.
- http://r4mzy.co.za/2017/05/01/select-partone-from-it-order-by-howhard-asc/
- http://r4mzy.co.za/2018/01/12/select-parttwo-from-it-order-by-howhard-asc/
