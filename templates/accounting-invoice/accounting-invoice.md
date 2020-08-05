### Accounting: Invoice-XNDP-22221010-000

- Prepared invoice entries in worklog 
- Exported invoice as PDF
- Copied invoice details to worklog
- Wrote draft invoice email
- Sent invoice via email
- Marked invoice as sent in worklog
- Added invoice to income spreadsheet

* Template command:
  . worklog template-out xndp 2222-ZZ accounting-invoice
  . worklog template-out xndp 2222-ZZ accounting-pricing

* Invoice-XNDP-22221010-000
  . Invoice Seq: 000
  . Invoice Number: Invoice-XNDP-22221010-000
  . Invoice Client: X NDP
  . Invoice Worker: Lindsay Bernath
  . Invoice Period: October 2222
  . Invoice Range: 2222-ZZ
  . Invoice Work Type: Task Work
  . Invoice Date: 2222-10-10
  . Invoice Due: 2222-DD-DD 

* Work-LindsayBernath-XNDP-2222ZZ-000LB
  . Work Seq: 000LB
  . Work Number: Work-LindsayBernath-XNDP-2222ZZ-000LB
  . Work Worker: Lindsay Bernath
  . Work Client: X NDP
  . Work Range: 2222-ZZ

* Invoice Process
  . worklog check-days
  . worklog notedata Invoice-XNDP-22221010-000
  . worklog cats
  . worklog cats Invoice-XNDP-22221010-000
  . worklog brackets Invoice-XNDP-22221010-000
  . worklog titles Invoice-XNDP-22221010-000
  . worklog times Invoice-XNDP-22221010-000
  . worklog totals Invoice-XNDP-22221010-000
  . worklog statuses Invoice-XNDP-22221010-000
  . worklog entries Invoice-XNDP-22221010-000
  . worklog notes Invoice-XNDP-22221010-000
  . worklog template-out accounting-pricing Invoice-XNDP-22221010-000
  . worklog invoice2export Invoice-XNDP-22221010-000; open Invoice-XNDP-22221010-000.html

* Invoice email

  ```
  Subject: Invoice for October 2222 - XNDP
  
  Hi Xavier,

  Attached is a detailed invoice for the work I've done in October 2222. On the first page, I've reported the numbers I think you're most interested in. Additional details follow. 

  I look forward to continuing our projects this week. Send me a quick reply when you've received this. And if you have any questions, send them my way.

  Lindsay
  ```
