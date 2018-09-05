Worklog CLI
==============

Deploy
------

To deploy this CLI. Run the deploy script:

```
chmod +x deploy.bash
. deploy.bash
```

The deploy script itself is quite short, and simply
symlinks the `worklog` script into the /usr/local/bin
folder.

```
#! /bin/bash
composer install
chmod +x "`pwd`/worklog"
ln -s "`pwd`/worklog" "/usr/local/bin/worklog"
```

Configure
---------

To configure the worklog cli, run:

```
worklog config
```

You will be asked:

- `Enter path to worklogs directory (enter to skip):`
- `Enter default worklog (enter to skip):`
- `Enter default invoice template name (enter to skip): `

And example configuration would be:

- Worklogs directory: `"/Users/myusername/Google Drive/Worklog"`
- Default worklog: `worklog-2020.txt`
- Default template name: `invoice-template-default`

The worklogs directory can be any absolute path. It should, ideally, live inside 
a cloud backed-up folder like Dropbox or Google Drive. 

The default worklog is the file that will be parsed by default. This should be a 
filename found inside the worklogs directory.

The default template name is the name of a template found inside the 
`worklog-cli/templates` folder. Currently, the only contributed template
is `invoice-template-default` - so use that.

Usage Examples
--------------

Once the worklog-cli is installed and configured properly, you will be able to
run the `worklog` command from terminal to interact with the worklog. In all
cases, it does not matter what folder you are in when you run this comman.

Here are some simple example use cases:

`worklog review today`  
Review the work you did today

`worklog review today $`  
Review paid work only you did today

`worklog review yesterday`  
Review the work you did yesterday

`worklog review mon fri`  
Review the work you did last monday through last friday

`worklog review aug`  
Review the work you did in august

`worklog review june aug`  
Review the work you did this just through this august

`worklog review 2020-10-12 2020-10-25`  
Review the work you did between two specific dates

Invoicing process
-----------------

To generate an invoice follow this process. In each command below replace _clientname_ with
the normalized name of the client (lowercase, letters and numbers only, no dashes or spaces -
for example Mike's Pizza becomes _mikespizza_) and replace _month_ with the three letter code
for the current month.

1. At the top of your worklog, add an entry for the client you want to invoice. Use the 
format below to add details about your client. Note that in the format below, some
data is not indented as you might expect. Copy this template exactly, and triple
check what you entered. In some cases this data is used to do financial math.

```
Mike's Pizza ($75)
------------

* Client Full name: Mike's Pizzeria and Subway House
* Client Short name: Mike's Pizza
* Client Address: Suite 123, 5555 Cheese Street, Sarnia, ON, N0N 1C0
* Client Rate: $75
* Client Contact Name: Mike Peterson
* Client Contact Email: mike.peterson@mikespizza.ca
* Client Tax Name : Ontario HST (13%)
* Client Tax Percent : 13%

* Statuses:
. In progress
. Delivered
. Recurring

* Projects:
. Pizza Convention 2020
. Tasks

* Tasks:
. Planning
. Development
. Troubleshooting
. Setup
```

2. Next, scroll down in your worklog to the first day of the month you'll be invoicing for, add another new entry
for the client. This time you'll add information about the current invoice. 

Copy this template exactly:

```
Sunday October 1st, 2020
========================

Mike's Pizza ($75)
------------

* Invoice Data
* Invoice Number: Invoice-AB-Mikes-20201031-101
* Invoice Date: 2020-10-31 
* Invoice Due: 2020-11-30
* Invoice Period: November 2020
* Invoice Work Type: Task work
```

As part of this step you'll need to choose an invoice number format. I suggest:

`Invoice-AB-Mikes-20201031-101`

That is:

- `Invoice-`
- `AB-`, your initials
- `Mikes`, client shortname
- `20201031`, last day of the invoice period
- `101`, a sequential number starting from 101

3. Run: `worklog days`

This will list all dates in your worklog. Look at the top and bottom of the list and make sure
there aren't any badly parsed dates. You don't want to see any dates in the future or in the past. 
In practice, bad dates usually show up in 1970. If there are any bad dates, find them in your worklog
file and fix them, then repeat this step to confirm all is good.

4. Run: `worklog cats`

This will list all categories in your worklog file. Categories are usually the name of your client,
so this should list all your clients. You want to confirm that the client your are invoicing appears
only once, and that the "brackets" column shows the correct hourly rate for the client. If your client
appears in the list more than once, then correct any typos, rate errors, or missing rates in your worklog
file and run this command again to confirm all is good.

5. Run: `worklog cats month clientname`

This will print all the categories for the given month that match clientname. Now that you've done the 
first two steps above, this step should show a single row - just your clientname and it's correct rate.

6. Run: `worklog brackets month clientname`

This will show every value placed in brackets next to your dates, categories or sittings. So you should
see the clients rate, and your should see the names of any project names, statuses, or task types you are 
using. Confirm that all project names are written once and spelled correctly. If you want to change the
name of a project, now is a good time to find-replace it throughout your worklog file.
 
7. Run: `worklog titles month clientname`

This will show the task title for all your sittings. In many cases, a task will only take one sitting, 
and so that sitting will have a unique task name that is only used once. That's fine. In other cases, it 
might that you many sittings to complete a single task, and so you could have many sittings that share
the same task title. Read through the output of this command and ensure that all task titles are simple
to understand, and that they are spelled correctly. This is also a good time to find and replace titles
in your worklog and then check back here.

8. Run: `worklog times month clientname`

This will output the total number of hours for each sitting, as well as every timestamp presented in
the subtasks of that sitting. The sittings are ordered from longest to shortest. The goal here is to 
confirm you that the total hours listed make sense. If one of your sittings takes 0 hours, confirm that
is correct. If one of your sittings takes 17 hours, you probably have an AM/PM typo in one of the time
brackets. Review the time brackets listed, search for the one that is incorrect, and fix it in the worklog.
The line number for the sitting is also mentioned in case it helps.

9. Run: `worklog totals month clientname`

This will output the total time spent on each task and project. This is where you match the time you've 
spent against the time you estimate, or intended to spend. If times are two low or two high, you can 
adjust them in your worklog but changing sitting times or adding multiplier brackets.

10. Run: `worklog notes month clientname`

This step is the bulk of the work. It will output all the completed subtask notes for 
every sitting. The goal is to read through every note, top to bottom, and make sure they make sufficient
sense. The more thorough you were when writing these, the less work you will have. In general, the notes
should be readable by the person receiving the invoice. That doesn't mean they need to understand every
technical word, but they should be able to read it as a properly constructed series of words. Read through 
everything correcting and improving subtask notes in the worklog as you go. 

11. Run: `worklog invoicehtml month clientname > ~/Google\ Drive/Invoices/invoice.html`

This line outputs an HTML version of the invoice. Output your invoice into an invoices folder, and open 
it in a browser. 

*Note:* You'll want to copy the template CSS files into this folder the first time. So if
you are using `invoice-template-default` in your config file, copy these into the invoices folder:

- `worklog-cli/templates/invoice-template-default/invoice-template-default.custom.css`
- `worklog-cli/templates/invoice-template-default/invoice-template-default.stackedit.css`

After viewing the invoice, confirm everything looks good. If you want to make any changes, change
them in your worklog and then generate the invoice file again.

12. Print the browser window as a PDF file. 

Adjust the scale of the print so that the summary shows up cleanly on page 1.
