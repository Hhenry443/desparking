ALTER TABLE payments
  CHANGE stripe_payment_intent_id paypal_order_id VARCHAR(255) NULL,
  CHANGE stripe_subscription_id paypal_subscription_id VARCHAR(255) NULL,
  CHANGE stripe_customer_id paypal_payer_id VARCHAR(255) NULL,
  ADD COLUMN paypal_capture_id VARCHAR(255) NULL AFTER paypal_order_id;

CREATE TABLE pending_checkouts (
  id VARCHAR(36) PRIMARY KEY,
  order_id VARCHAR(255) NULL,
  subscription_id VARCHAR(255) NULL,
  type ENUM('booking', 'subscription', 'extension') NOT NULL,
  carpark_id INT NOT NULL,
  booking_id INT NULL,
  user_id INT NULL,
  vehicle_id INT NULL,
  registration VARCHAR(20) NULL,
  name VARCHAR(255) NOT NULL,
  email VARCHAR(255) NULL,
  start DATETIME NOT NULL,
  end DATETIME NOT NULL,
  amount INT NULL,
  owner_amount INT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_order_id (order_id),
  INDEX idx_subscription_id (subscription_id)
);
