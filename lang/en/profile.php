<?php

return [
    'title' => 'Profile',
    'edit' => 'Edit Profile',
    'member_since' => 'Member since :date',
    'verified' => 'Verified',
    'unverified' => 'Unverified',

    // Information
    'information' => [
        'title' => 'Profile Information',
        'description' => 'Update your account\'s profile information, email address, and phone number.',
        'name' => 'Name',
        'email' => 'Email',
        'phone' => 'Phone Number',
        'phone_hint' => 'Enter your phone number with country code (e.g., +1234567890)',
        'saved' => 'Saved.',
        'saving' => 'Saving...',
        'save_verify_phone' => 'Save & Verify Phone',
    ],

    // Password
    'password' => [
        'title' => 'Update Password',
        'description' => 'Ensure your account is using a long, random password to stay secure.',
        'current' => 'Current Password',
        'new' => 'New Password',
        'confirm' => 'Confirm Password',
        'saved' => 'Saved.',
    ],

    // Delete account
    'delete' => [
        'title' => 'Delete Account',
        'description' => 'Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.',
        'button' => 'Delete Account',
        'confirm_title' => 'Are you sure you want to delete your account?',
        'confirm_description' => 'Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.',
        'password_placeholder' => 'Password',
    ],

    // Email verification
    'email_verification' => [
        'unverified' => 'Your email address is unverified.',
        'resend' => 'Click here to re-send the verification email.',
        'sent' => 'A new verification link has been sent to your email address.',
    ],

    // Phone verification
    'phone_verification' => [
        'unverified' => 'Your phone number is unverified.',
        'verify_link' => 'Click here to verify your phone number.',
        'change_warning' => 'Changing your phone number will require OTP verification. You\'ll be redirected to verify the new number after saving.',
    ],

    // Status messages
    'status' => [
        'verification_link_sent' => 'A new verification link has been sent to your email address.',
        'profile_updated_verify_email' => 'Profile updated. Please verify your new email address.',
        'profile_updated_verify_phone' => 'Profile updated. Please verify your new phone number.',
        'profile_updated_verify_both' => 'Profile updated. Please verify your new email and phone number.',
    ],
];
