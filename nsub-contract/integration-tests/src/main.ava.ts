import { Worker, NearAccount, toYocto, NEAR} from 'near-workspaces';
import anyTest, { TestFn } from 'ava';

const test = anyTest as TestFn<{
  worker: Worker;
  accounts: Record<string, NearAccount>;
}>;

test.beforeEach(async (t) => {
  // Init the worker and start a Sandbox server
  const worker = await Worker.init();

  // Deploy contract
  const root = worker.rootAccount;
  const contract = await root.createSubAccount('nsubcontract');
  // Get wasm file path from package.json test script in folder above
  await contract.deploy(
    process.argv[2],
  );

  //create more accounts
  const illa = await root.createSubAccount('illa' , {
    initialBalance: NEAR.parse("1000 N").toJSON()
  });

  const alex = await root.createSubAccount('alex' , {
    initialBalance: NEAR.parse("50 N").toJSON()
  });


  // Save state for test runs, it is unique for each test
  t.context.worker = worker;
  t.context.accounts = { root, contract , illa, alex};


});

test.afterEach.always(async (t) => {
  // Stop Sandbox server
  await t.context.worker.tearDown().catch((error) => {
    console.log('Failed to stop the Sandbox:', error);
  });
});

// TODO: Tests will be done s00N ~~~~

// test('returns the default greeting', async (t) => {
//   const { contract } = t.context.accounts;
//   const message: string = await contract.view('get_greeting', {});
//   t.is(message, 'Hello');
// });

// test('changes the message', async (t) => {
//   const { root, contract } = t.context.accounts;
//   await root.call(contract, 'set_greeting', { message: 'Howdy' });
//   const message: string = await contract.view('get_greeting', {});
//   t.is(message, 'Howdy');
// });

// test('set tax to 10%' , async (t) => {
//   const {root, contract} = t.context.accounts;
//   await root.call(contract, 'setTax' , {newTax: 10 });
//   const rs: bigint = await contract.view('getTax' , {});
//   t.is(rs, BigInt(10));
// });

// test('get tax' , async(t)=>{
//   const {root, contract}= t.context.accounts;
//   const rs = await contract.view('getTax' , {});
//   console.log("Current tax : " + rs );
// });

// test('pay 10N and got reduced by 10% tax' , async(t) => {
//   const {root,contract,illa,alex} = t.context.accounts;
//   // const rs:string = await root.call(contract, 'pay' , {price: toYocto("1") , receiver:"tientien.testnet"});
//   const rs = await illa.call(contract, "pay" , { amount: NEAR.parse("10").toString(), receiver: alex.accountId }, { attachedDeposit: NEAR.parse("21").toString() });
//   const alexNewBalance = await alex.balance();

//   console.log("result: " , rs);
//   console.log("Alex new balance:  " , alexNewBalance);
// });