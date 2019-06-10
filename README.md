# Site Map Web Scraper

- __**Client:**__ BKD
- __**Timeframe:**__ Early July to September 2017
- __**Technologies:**__ PHP, PHP libraries
- __**Description:**__ I was originally tasked with making an Excel spreadsheet by hand, which would include a lot of information for each of the hundreds of pages of the BKD site. I set out to create a script that would automate this for me. In the end, I created something that not only met the original expectations, but largely exceeded them by allowing us to have a new, updated version whenever we want. \*Note: All of the information in this script is pulled from public information on BKD’s site. Any internal notes have been removed as well as some small amounts of information to protect privacy.
- __**Technical Overview:**__ The backbone of this script is the PHP function file_get_contents. From there, I used a third-party library called Simple PHP DOM to navigate the called file, and filter it into its component parts. I then used a third-party library called PHPExcel, to output everything into an Excel spreadsheet.
- __**Next Steps:**__ This project is finished.
