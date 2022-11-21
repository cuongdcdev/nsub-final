import { THIRTY_TGAS } from "./near-wallet";
import { utils } from "near-api-js";

export class NSub {
  constructor({ contractId, walletToUse }) {
    this.contractId = contractId;
    this.wallet = walletToUse;
  }

/**
 * 
 * @param {float} price price in float  
 * @param {string} receiver receiver NEAR wallet 
 * @param {string} callbackurl call back Url 
 * @returns 
 */
  async pay(price, receiver, callbackurl = "") {
    let yNear = utils.format.parseNearAmount(price);

    return await this.wallet.callMethod({
      contractId: this.contractId, 
      method: 'pay',
      args: { price: yNear, receiver: receiver  },
      gas: THIRTY_TGAS,
      deposit: yNear,
      callbackUrl: callbackurl
    });

  }

/**
 * 
 * @param {float} price price in float  
 * @param {string} receiver receiver NEAR wallet 
 * @param {string} callbackurl call back Url 
 * @returns 
 */
  async donate( price, receiver, callbackurl = "" ){
    let yNear = utils.format.parseNearAmount(price);

    return await this.wallet.callMethod({
      contractId: this.contractId, 
      method: 'pay',
      args: { price: yNear, receiver: receiver  },
      gas: THIRTY_TGAS,
      deposit: yNear,
      callbackUrl: callbackurl
    });
  }

  //get tx result 
  async getTxResult(txhash){
    let rs = await this.wallet.getTransactionResult(txhash);
    console.log( "txhashresult" , rs );
  }

}