# LTW Project
Project description for the 2026 edition of the Web Languages and Technologies course.

## Objective
Develop a website that facilitates the management of a gym. The platform should allow members to browse and enroll in fitness classes, check the availability of equipment in the main training area, and book sessions with personal trainers. Additionally, the platform should support robust search and filter capabilities, detailed profiles for trainers and classes, and feedback mechanisms.

To create this website, students should:

- Implement an SQLite database to maintain records of members, trainers, classes, equipment, bookings, and reviews.
- Design responsive web pages using HTML and CSS that reflect the gym's brand and interface.
- Utilize PHP to dynamically generate web pages by interacting with the database.
- Enhance interactivity and responsiveness on the client side using Javascript, incorporating 
features like live schedule updates and asynchronous data refreshes with Ajax.


## Workgroups
Students will complete this project in groups of three.
In classes where the number of students is not a multiple of three, one or two groups of two students will be created.
Students should contact their practical class teachers during the weekly class (or using Slack) to establish these workgroups.


## Requirements
The requirements are deliberately broad to allow each group the freedom to innovate and create a unique website. Let your creativity flow!

This gym management platform features three types of users: members who subscribe to the gym and use its facilities, trainers who teach classes and offer personal training, and admins who manage the platform's overall operations.

### Base Requirements
The following requirements are the minimum expected for all projects:

All users should be able to:

- Register a new account.
- Log in and out.
- Edit their profile, including name, username, password, and profile photo.

### Members should be able to:

- Browse the schedule of available fitness classes (e.g., yoga, spinning, HIIT, pilates), filtering by type, trainer, day, or time.
- Enroll in and cancel enrollment from upcoming classes, subject to capacity limits.
- View trainer profiles, including their specializations and the classes they teach.
- Check the current availability of equipment in the main training area (e.g., treadmills, bikes, weight benches).
- Leave ratings and reviews for classes they have attended.

### Trainers should be able to:

- Manage their public profile, including bio, specializations, and certifications.
- View the roster of members enrolled in their classes.
- Track and manage their assigned class schedule.

### Admins should be able to:

- Manage members and trainers (create, update, and deactivate accounts).
- Manage the class catalog (create, edit, and remove classes) and assign trainers to them.
- Manage equipment in the main training area (add, update availability status, and remove items).
- Elevate a user to admin status.
- Oversee and ensure the smooth operation of the entire system.

### Extra Requirements
Some suggested additional requirements to make each project unique. You do not need to implement all of these — choose a set that interests your group and gives your project a distinct identity.

- Personal Training Bookings: Members can browse trainer availability and book one-on-one personal training sessions.
- Membership Plans: Define tiered membership plans (e.g., basic, premium) with different access levels, and allow members to subscribe to or upgrade their plan.
- Equipment Reservation: Members can reserve a specific piece of equipment for a time slot in the main training area.
- Class Waitlist: When a class is full, members can join a waitlist and are automatically enrolled when a spot opens.
- Member Progress Tracking: Members can log workouts, set fitness goals, and track progress over time with charts or statistics.
- Notification System: Send in-app or email notifications for class reminders, booking confirmations, waitlist updates, or membership renewals.
- Trainer Analytics Dashboard: Trainers can view attendance statistics, ratings summaries, and engagement data for their classes.
- Admin Analytics Dashboard: Admins can view gym-wide metrics such as most popular classes, equipment usage, and member retention.
- Nutrition Plans: Trainers can create and assign nutrition plans to their members, with meal and calorie tracking.
- Disputes and Feedback: Members can report issues (e.g., equipment malfunction, class cancellations) and admins can manage and respond to these reports.
- REST API: Expose a public API that allows third-party applications to query class schedules, trainer profiles, and equipment availability.
- Promotional Features: Admins can highlight specific classes or trainers on the homepage through featured promotions.


Remember, these are just suggestions to inspire further development and innovation. You may also come up with your own additional requirements.

## Required Technologies
Ensure the application incorporates the following technologies:

- HTML: For structuring the web content.
- CSS: For styling the web pages. Aim for a mobile-first, responsive design.
- PHP: For server-side scripting.
- JavaScript: For client-side scripting.
- Ajax/JSON: For asynchronous web page updates.
- PDO/SQL: For database interactions using SQLite.

## Security Measures
Prioritize security to safeguard user data and interactions:

- Protect against SQL injection by using prepared statements with PDO.
- Mitigate Cross-Site Scripting (XSS) attacks by sanitizing user input and output.
- Prevent Cross-Site Request Forgery (CSRF) by implementing anti-CSRF tokens.
- Implement secure password storage with proper hashing and salting techniques.

## Code Quality
Maintain high standards of code quality:

- Write clean, and readable code.
- Follow a consistent naming convention and coding style.
- Organize files and directories logically.

## Design Consistency
Design a user-friendly interface:

- Adopt a clean and intuitive design that is consistent across the website.
- Use generic CSS rules where possible to ensure uniformity.
- Ensure compatibility with various devices, especially mobile phones.

## Restrictions
Adhere to the following restrictions:

- You cannot use frameworks such as Laravel, jQuery, or Bootstrap.
- Consult with your instructor before using any small helper libraries.

## Work Plan
Weeks 7–9 build toward the first delivery; weeks 10–13 complete the full project for the final delivery.

- Week 7: Mockups and navigation diagrams for the main pages. First draft of the database design. Choose the three pages for the first delivery.
- Week 8: Write semantic HTML for the three selected pages. Start writing CSS — begin with base/reset rules, then define reusable component styles.
- Week 9: Finalize CSS for the three pages — specific page and component rules, responsive design, file and layer organization. First delivery.
- Week 10: Finalize database script and create the database. Start implementing main pages in PHP.
- Week 11: Most main pages implemented. Start working on secondary features.
- Week 12: Continue working on secondary features. Start working on Javascript and Ajax.
- Week 13: REST API or other secondary features. Security concerns. 

## Testing and code cleanup.
We recommend that students adopt an agile methodology. Don't start by planning every little detail right from the start, as you risk ending up with a great plan but a poor implementation. Be aware of code organization and quality from the beginning.

## Evaluation
The project is evaluated in two deliveries, together worth 50% of the final course grade.

### First Delivery — 10%
Evaluated on the HTML and CSS of three selected pages:

- Semantic HTML: correct use of structural and semantic elements.
- CSS organization: base/reset rules first, then reusable component styles, then specific page or variant rules.
- File and layer organization: logical separation of concerns across files or CSS layers.
- Responsive design: layout adapts correctly to different screen sizes.


### Final Delivery — 40%
Evaluated on the complete project across the following topics:

- Complexity (e.g., implemented features).
- Security (e.g., XSS, CSRF, injection, password storage).
- Technology (e.g., correct usage of HTML, CSS, Javascript, Ajax, No frameworks).
- Quality (e.g., code quality, file organization, consistency).
- User Interface (e.g., usability, consistency).

The goal is to implement the requirements in a unique way that meets the project objectives. Using code from other sources without proper attribution will lead to the student failing the course.

## Delivery
Use this template for your README.md file.

### First Delivery
Delivery at the end of week 9 until the 24th of April at 23:59 (WEST).
Submit the HTML and CSS files for your three selected pages.
You must create a tag named "first-delivery-v1" on the commit you wish to deliver.

```
git tag first-delivery-v1
git push origin first-delivery-v1
```
### Final Delivery
Delivery at the end of week 13 until the 29th of May at 23:59 (WEST).
Demo in last week's practical class (using the delivered version).
You must create a tag named "final-delivery-v1" on the commit you wish to deliver.

```
git tag final-delivery-v1
git push origin final-delivery-v1
```
To test if everything is correct, you should be able to clone the project into an empty directory:

git clone <your_repo_url>
git checkout final-delivery-v1
php -S localhost:9000
And view your website at http://localhost:9000/.