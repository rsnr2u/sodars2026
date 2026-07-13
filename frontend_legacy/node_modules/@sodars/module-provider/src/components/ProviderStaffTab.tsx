import React, { useState } from 'react';
import { useStaff } from '../hooks/useStaff';
import { useBranches } from '../hooks/useBranches';
import { StaffStatus, StaffRole } from '../enums';

interface ProviderStaffTabProps {
  providerId: string;
}

export const ProviderStaffTab: React.FC<ProviderStaffTabProps> = ({ providerId }) => {
  const {
    data: staffList,
    isLoading: loadingStaff,
    createStaff,
    deleteStaff,
    transferBranch,
    updateStaff,
    activateStaff,
    deactivateStaff,
  } = useStaff({ providerId });
  const { data: branches, isLoading: loadingBranches } = useBranches(providerId);

  const [showCreateModal, setShowCreateModal] = useState(false);
  const [showTransferModal, setShowTransferModal] = useState<string | null>(null);

  const [firstName, setFirstName] = useState('');
  const [lastName, setLastName] = useState('');
  const [email, setEmail] = useState('');
  const [phoneVal, setPhoneVal] = useState('');
  const [role, setRole] = useState<StaffRole>(StaffRole.Technician);
  const [employeeCode, setEmployeeCode] = useState('');
  const [selectedBranchId, setSelectedBranchId] = useState('');

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!firstName || !email || !employeeCode) return;
    try {
      await createStaff({
        id: `staff-${Date.now()}`,
        providerId,
        branchId: selectedBranchId || undefined,
        employeeCode,
        name: { firstName, lastName },
        email: { value: email },
        phone: { countryCode: '61', number: phoneVal || '000000000' },
        designation: role,
        status: StaffStatus.Active,
        joiningDate: Date.now(),
        createdAt: Date.now(),
        updatedAt: Date.now(),
        version: 1,
        isActive: true,
      });
      setFirstName('');
      setLastName('');
      setEmail('');
      setPhoneVal('');
      setEmployeeCode('');
      setRole(StaffRole.Technician);
      setSelectedBranchId('');
      setShowCreateModal(false);
    } catch (err) {
      alert(err instanceof Error ? err.message : String(err));
    }
  };

  const handleTransfer = async (staffId: string, destBranchId: string) => {
    try {
      if (!destBranchId) {
        await updateStaff(staffId, { branchId: undefined });
      } else {
        await transferBranch(staffId, destBranchId);
      }
      setShowTransferModal(null);
    } catch (err) {
      alert(err instanceof Error ? err.message : String(err));
    }
  };

  const toggleStaffStatus = async (staffId: string, currentActive: boolean) => {
    try {
      if (currentActive) {
        await deactivateStaff(staffId);
      } else {
        await activateStaff(staffId);
      }
    } catch (err) {
      alert(err instanceof Error ? err.message : String(err));
    }
  };

  if (loadingStaff || loadingBranches) {
    return <div className="text-slate-400 text-xs py-8 text-center">Loading staff coordinate list...</div>;
  }

  return (
    <div className="space-y-6 font-sans">
      <div className="flex justify-between items-center">
        <div>
          <h3 className="text-base font-bold text-slate-900">Distribution Staff Registry</h3>
          <p className="text-xs text-slate-500 mt-0.5">Manage operator profiles, role assignments, and branch distribution transfers.</p>
        </div>
        <button
          onClick={() => setShowCreateModal(true)}
          className="px-3.5 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-bold transition-all flex items-center space-x-1 cursor-pointer"
        >
          <span>+ Add Staff</span>
        </button>
      </div>

      <div className="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm">
        <table className="w-full text-left text-xs border-collapse">
          <thead>
            <tr className="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold uppercase tracking-wider text-[10px]">
              <th className="p-4">Staff Member</th>
              <th className="p-4">Code</th>
              <th className="p-4">Designation</th>
              <th className="p-4">Assigned Branch</th>
              <th className="p-4">Status</th>
              <th className="p-4 text-right">Actions</th>
            </tr>
          </thead>
          <tbody className="divide-y divide-slate-100">
            {staffList.map(s => {
              const matchedBranch = branches.find(b => b.id === s.branchId);
              return (
                <tr key={s.id} className="hover:bg-slate-50/50 transition-colors">
                  <td className="p-4">
                    <div className="font-bold text-slate-900">{s.name.firstName} {s.name.lastName}</div>
                    <div className="text-[10px] text-slate-450 font-mono mt-0.5">{s.email.value}</div>
                  </td>
                  <td className="p-4 font-mono text-slate-700">{s.employeeCode}</td>
                  <td className="p-4">
                    <span className="px-2 py-0.5 bg-indigo-50 text-indigo-700 font-bold rounded text-[9px] uppercase">
                      {s.designation}
                    </span>
                  </td>
                  <td className="p-4 text-slate-700">
                    {matchedBranch ? (
                      <span className="font-medium">{matchedBranch.name}</span>
                    ) : (
                      <span className="text-slate-400 italic">Unassigned (HQ)</span>
                    )}
                  </td>
                  <td className="p-4">
                    <button
                      onClick={() => toggleStaffStatus(s.id, s.isActive)}
                      className={`px-2 py-0.5 rounded text-[10px] font-bold border transition-colors cursor-pointer ${
                        s.isActive
                          ? 'bg-emerald-50 text-emerald-700 border-emerald-100 hover:bg-emerald-100'
                          : 'bg-rose-50 text-rose-700 border-rose-100 hover:bg-rose-100'
                      }`}
                    >
                      {s.isActive ? 'Active' : 'Inactive'}
                    </button>
                  </td>
                  <td className="p-4 text-right space-x-2">
                    <button
                      onClick={() => setShowTransferModal(s.id)}
                      className="text-indigo-600 hover:text-indigo-800 font-bold text-[10px] uppercase cursor-pointer"
                    >
                      Transfer
                    </button>
                    <button
                      onClick={() => deleteStaff(s.id)}
                      className="text-rose-600 hover:text-rose-800 font-bold text-[10px] uppercase cursor-pointer"
                    >
                      Delete
                    </button>
                  </td>
                </tr>
              );
            })}

            {staffList.length === 0 && (
              <tr>
                <td colSpan={6} className="text-center py-12 text-slate-400 italic">
                  No staff members registered in the database directory.
                </td>
              </tr>
            )}
          </tbody>
        </table>
      </div>

      {/* Create Modal */}
      {showCreateModal && (
        <div className="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-50 flex items-center justify-center p-4">
          <div className="bg-white rounded-xl shadow-xl max-w-md w-full border border-slate-250 p-6 space-y-4">
            <div className="flex justify-between items-center border-b border-slate-100 pb-3">
              <h3 className="text-sm font-bold text-slate-900 uppercase tracking-wider">Register New Staff Member</h3>
              <button onClick={() => setShowCreateModal(false)} className="text-slate-400 hover:text-slate-655 cursor-pointer">✕</button>
            </div>

            <form onSubmit={handleSubmit} className="space-y-4 text-xs">
              <div className="grid grid-cols-2 gap-4">
                <div className="space-y-1">
                  <label className="font-semibold text-slate-600">First Name</label>
                  <input
                    value={firstName}
                    onChange={e => setFirstName(e.target.value)}
                    placeholder="John"
                    className="w-full border border-slate-200 rounded p-2 outline-none focus:border-indigo-500 text-xs"
                    required
                  />
                </div>
                <div className="space-y-1">
                  <label className="font-semibold text-slate-600">Last Name</label>
                  <input
                    value={lastName}
                    onChange={e => setLastName(e.target.value)}
                    placeholder="Doe"
                    className="w-full border border-slate-200 rounded p-2 outline-none focus:border-indigo-500 text-xs"
                  />
                </div>
              </div>

              <div className="grid grid-cols-2 gap-4">
                <div className="space-y-1">
                  <label className="font-semibold text-slate-600">Email Address</label>
                  <input
                    type="email"
                    value={email}
                    onChange={e => setEmail(e.target.value)}
                    placeholder="john.doe@corporate.com"
                    className="w-full border border-slate-200 rounded p-2 outline-none focus:border-indigo-500 text-xs"
                    required
                  />
                </div>
                <div className="space-y-1">
                  <label className="font-semibold text-slate-600">Employee Code</label>
                  <input
                    value={employeeCode}
                    onChange={e => setEmployeeCode(e.target.value)}
                    placeholder="e.g. EMP-101"
                    className="w-full border border-slate-200 rounded p-2 outline-none focus:border-indigo-500 text-xs"
                    required
                  />
                </div>
              </div>

              <div className="grid grid-cols-2 gap-4">
                <div className="space-y-1">
                  <label className="font-semibold text-slate-600">Mobile Phone</label>
                  <input
                    value={phoneVal}
                    onChange={e => setPhoneVal(e.target.value)}
                    placeholder="e.g. 0400000000"
                    className="w-full border border-slate-200 rounded p-2 outline-none focus:border-indigo-500 text-xs"
                  />
                </div>
                <div className="space-y-1">
                  <label className="font-semibold text-slate-600">Designation Role</label>
                  <select
                    value={role}
                    onChange={e => setRole(e.target.value as StaffRole)}
                    className="w-full border border-slate-200 rounded p-2 outline-none focus:border-indigo-500 bg-white text-xs"
                  >
                    <option value={StaffRole.BranchManager}>Branch Manager</option>
                    <option value={StaffRole.OperationsManager}>Operations Manager</option>
                    <option value={StaffRole.Admin}>Admin</option>
                    <option value={StaffRole.Technician}>Technician</option>
                    <option value={StaffRole.Installer}>Installer</option>
                    <option value={StaffRole.Accountant}>Accountant</option>
                  </select>
                </div>
              </div>

              <div className="space-y-1">
                <label className="font-semibold text-slate-600">Initial Branch Assignment</label>
                <select
                  value={selectedBranchId}
                  onChange={e => setSelectedBranchId(e.target.value)}
                  className="w-full border border-slate-200 rounded p-2 outline-none focus:border-indigo-500 bg-white text-xs"
                >
                  <option value="">Unassigned (HQ / Office)</option>
                  {branches.map(b => (
                    <option key={b.id} value={b.id}>{b.name}</option>
                  ))}
                </select>
              </div>

              <button
                type="submit"
                className="w-full py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-lg font-bold transition-all text-xs cursor-pointer"
              >
                Register Staff Profile
              </button>
            </form>
          </div>
        </div>
      )}

      {/* Transfer Modal */}
      {showTransferModal && (
        <div className="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-50 flex items-center justify-center p-4">
          <div className="bg-white rounded-xl shadow-xl max-w-sm w-full border border-slate-250 p-6 space-y-4">
            <div className="flex justify-between items-center border-b border-slate-100 pb-3">
              <h3 className="text-sm font-bold text-slate-900 uppercase tracking-wider">Transfer Distribution Branch</h3>
              <button onClick={() => setShowTransferModal(null)} className="text-slate-400 hover:text-slate-655 cursor-pointer">✕</button>
            </div>

            <div className="space-y-3 text-xs">
              <p className="text-slate-500">Select the destination branch to transfer this staff member to:</p>
              <div className="space-y-2 max-h-60 overflow-y-auto">
                <button
                  onClick={() => handleTransfer(showTransferModal, '')}
                  className="w-full text-left p-2.5 bg-slate-50 hover:bg-indigo-50 border border-slate-200 hover:border-indigo-300 rounded font-medium transition-colors cursor-pointer"
                >
                  Unassign (HQ / Office)
                </button>
                {branches.map(b => (
                  <button
                    key={b.id}
                    onClick={() => handleTransfer(showTransferModal, b.id)}
                    className="w-full text-left p-2.5 bg-slate-50 hover:bg-indigo-50 border border-slate-200 hover:border-indigo-300 rounded font-medium transition-colors flex justify-between items-center cursor-pointer"
                  >
                    <span>{b.name}</span>
                  </button>
                ))}
              </div>
            </div>
          </div>
        </div>
      )}
    </div>
  );
};
export default ProviderStaffTab;
