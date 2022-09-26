<?php 
use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;
class Producer {

    /**
     * Sends a message to the bill queue.
     * 
     * @param string $message
     */
    public function execute($message)
    {
        /**
         * Create a connection to RabbitAMQP
         */
        $connection = new AMQPConnection(RABBIT_HOST,RABBIT_PORT,RABBIT_USER,RABBIT_PASS);            

        /** @var $channel AMQPChannel */
        $channel = $connection->channel();        
        $channel->exchange_declare('sp_exchange', 'direct', false, true, false);
        
        $msg = new AMQPMessage($message, ['delivery_mode' => 2]);
        
        $channel->basic_publish(
            $msg,           #message 
            'sp_exchange',             #exchange
            'gepg.bill.out'     #routing key
            );
        
        

        $channel->close();
        $connection->close();
    }
}
