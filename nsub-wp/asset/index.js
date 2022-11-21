import 'regenerator-runtime/runtime';
import { Wallet } from './near-wallet';
import { NSub } from './near-interface';
import "./custom.css";
const CONTRACT_NAME = "dev-1666516150970-72230590021052";
// When creating the wallet you can optionally ask to create an access key
// Having the key enables to call non-payable methods without interrupting the user to sign
const wallet = new Wallet({ createAccessKeyFor: CONTRACT_NAME })
const nSubContract = new NSub({ contractId: CONTRACT_NAME, walletToUse: wallet }); 

// const wallet = new Wallet({ createAccessKeyFor: process.env.CONTRACT_NAME })
// const nSubContract = new NSub({ contractId: process.env.CONTRACT_NAME, walletToUse: wallet }); 
//neu contractId = null => khong yeu cau connect voi contract => login ko mat gas 

// Setup on page load
window.onload = async () => {
  window.isSignedIn = await wallet.startUp();
  window.nSubContract = nSubContract;

  console.log("NEAR account ready!");
  window.initNSub();
};