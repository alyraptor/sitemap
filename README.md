- Client: BKD
- Timeframe: Early July to September 2017
- Technologies: PHP, PHP libraries
- Description: I was originally tasked with making an Excel spreadsheet by hand, which would include a lot of information for each of the hundreds of pages of the BKD site. I set out to create a script that would automate this for me. In the end, I created something that not only met the original expectations, but largely exceeded them by allowing us to have a new, updated version whenever we want. *Note: All of the information in this script is pulled from public information on BKD’s site, which has since been restructured. All internal notes and some extra helper functionality has been removed from the repository.
- Technical Overview: The backbone of this script is the PHP function file_get_contents. From there, I used a third-party library called Simple PHP DOM to navigate the called file, and filter it into its component parts. I then used a third-party library called PHPExcel, to output everything into an Excel spreadsheet.
- Next Steps: This project is finished.
