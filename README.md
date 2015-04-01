Message queue for emails extension for Yii 2
============================================

This extension provides an email solution via message queueing for Yii 2.
It comes ready for [RabbitMQ](https://www.rabbitmq.com/). For other MQs you can provide your own "mqMessageClass" (see below) as long as it implements the QueueMessageInterface.
You need of course a message queue like [RabbitMQ](https://www.rabbitmq.com/) installed.

**Be aware that this extension is stable but not ready for production yet.**

Please submit issue reports and pull requests to <https://github.com/chrisb88/yii2-mailqueue>.

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist cbm/yii2-mailqueue
```

or add

```json
"cbm/yii2-mailqueue": "*"
```

to the require section of your composer.json.

Usage
-----

Sample configuration:

```php
return [
    //....
    'components' => [
        // This is the message queue mailer config, it substitutes the original mailer config
        'mailer' => [
            'class' => 'cbm\mailqueue\QueueMailer',
            'mqMessageClass' => 'cbm\mailqueue\RabbitMessage',
            'mqConfig' => [
                'host' => 'localhost',
                'port' => 5672,
                'user' => 'guest',
                'password' => 'guest',
                'queue' => 'email_task',
                'publishRoutingKey' => 'email_task',
            ],
        ],
        // You also need the original yii swift mailer but with a different name
        'emailMailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            //....
        ],
    ],
];
```

You can then send an email to the queue as follows (no change to the original implementation):

```php
Yii::$app->mailer->compose('contact/html')
     ->setFrom('from@domain.com')
     ->setTo($form->email)
     ->setSubject($form->subject)
     ->send();
```

Don't forget to rename your original yii swift mailer to "emailMailer". It is still needed to really send the emails.

Get the email consumer running
------------------------

Copy the file console/controller under your application console/controller (or where ever your console commands are) and run

```
yii email-task
```

The consumer will then listen to email messages and send them using the original yii swift mailer.

