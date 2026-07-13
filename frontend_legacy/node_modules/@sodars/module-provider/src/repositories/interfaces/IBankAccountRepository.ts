import { BankAccount } from '../../types';

export interface IBankAccountRepository {
  findBankAccount(providerId: string): Promise<BankAccount | null>;
  saveBankAccount(bankAccount: BankAccount): Promise<BankAccount>;
}
export default IBankAccountRepository;
