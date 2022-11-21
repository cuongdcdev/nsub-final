import { NearBindgen, near, call, view } from 'near-sdk-js';
import { assert } from 'near-sdk-js';

@NearBindgen({})
class NSub {
  // greeting: string = "Hello";
  tax:bigint = BigInt(0);

  @call( {privateFunction: true} )
  setTax({newTax} : {newTax: bigint}): void{
    if( newTax <= 30 && newTax >= 0 ){
      //max tax 30%
      this.tax = newTax;
      near.log("Set new tax to: " + this.tax);
    }else{
      near.log("New tax must between 0 to 30");
    }
  }

  @view({})
  getTax():bigint{
    return this.tax;
  }

  @call({ payableFunction: true })
  pay({ price, receiver }: { price: bigint, receiver: string }):string {
    let amount: bigint = BigInt(price);
    let sender = near.signerAccountId();
    let depositedAmount: bigint = near.attachedDeposit() as bigint;
    let amountForReceiver:bigint = amount;
  
    assert(depositedAmount >= amount, `Pay at least ${amount}, you deposited ${depositedAmount}`);
    assert( amount > 0 , `Amount must > 0 ` );
    assert(receiver.length > 0, "Receiver address can't be empty ");

    //contract earn % fee from each payment
    if( this.tax > 0 ){
      amountForReceiver = amount - amount*BigInt(this.tax)/BigInt(100);
    }

    //transfer token leftover to sender
    if (depositedAmount > amount) {
      let leftover:bigint = depositedAmount - amount;
      near.promiseBatchActionTransfer(near.promiseBatchCreate(sender), leftover);
      near.log(`Refund amount leftover: ${leftover} to ${sender}`);
    }

    //transfer token to receiver 
    near.promiseBatchActionTransfer(near.promiseBatchCreate(receiver), amountForReceiver);

    //log the result 
    let rs = JSON.stringify({
      sender: sender,
      receiver: receiver,
      order_amount: price,
      received_amount: amountForReceiver,
      deposited_amount: depositedAmount,
      tax: this.tax,
      created_at: near.blockTimestamp(),
    }, (k, v) => typeof v == 'bigint' ? v.toString() : v);

    near.log(rs);
    
    return rs;

  }

}